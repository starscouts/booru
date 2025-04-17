const { WebSocketServer } = require('ws');
const devices = {};

const wss = new WebSocketServer({ port: 8080 });
const uuid = require('uuid-v4');

wss.on('connection', function connection(ws, req) {
    const ip = req.headers['x-forwarded-for'].split(',')[0].trim();
    const id = uuid();
    if (!devices[ip]) devices[ip] = null;

    ws.on('error', console.error);

    ws.on('message', function message(_data) {
        console.log(devices);

        try {
            const data = JSON.parse(_data);
            console.log(data);

            if (data.type === "init") {
                if (data.code) {
                    ws.watch = true;
                    ws.loaded = true;

                    if (!devices[ip]) devices[ip] = {
                        type: "watch",
                        socket: ws,
                        code: data.code,
                        clients: {}
                    }
                } else if (data.key) {
                    if (devices[ip] && devices[ip].code === data.key) {
                        ws.watch = false;
                        ws.loaded = true;

                        devices[ip].clients[id] = ws;
                        devices[ip].socket.send(JSON.stringify({
                            type: "status",
                            enabled: true,
                            count: devices[ip].clients.length
                        }));

                        ws.send(JSON.stringify({
                            type: "accepted"
                        }));
                    } else {
                        ws.send(JSON.stringify({
                            type: "rejected"
                        }));
                    }
                } else {
                    ws.send(JSON.stringify({
                        type: "invalid_init"
                    }));
                }
            } else if (data.type === "shake" && ws.loaded && ws.watch) {
                for (let socket of Object.values(devices[ip].clients)) {
                    try {
                        if (socket.loaded && !socket.watch) {
                            socket.send(JSON.stringify({
                                type: "shake"
                            }));
                        }
                    } catch (e) {}
                }
            } else {
                ws.send(JSON.stringify({
                    type: "invalid_global"
                }));
            }
        } catch (e) {
            console.error(e);
        }

        console.log("----------");
    });

    ws.on('close', () => {
        if (!ws.watch) {
            console.log("Remove " + id);
            if (devices[ip]) delete devices[ip].clients[id];

            if (Object.values(devices[ip].clients).filter(i => i).length <= 0) devices[ip].socket.send(JSON.stringify({
                type: "status",
                enabled: false
            }));
        } else {
            for (let socket of Object.values(devices[ip].clients)) {
                try {
                    if (socket.loaded && !socket.watch) {
                        socket.send(JSON.stringify({
                            type: "disconnect"
                        }));
                    }
                } catch (e) {}
            }

            delete devices[ip];
        }
    });

    ws.send(JSON.stringify({
        type: "init"
    }));
});