let lastShake = 0;

function connect() {
    const ws = new WebSocket("wss://booru.equestria.dev/_shakeapi/socket");

    ws.onmessage = (event) => {
        let data = JSON.parse(event.data);
        console.log(data, new Date().toISOString(), new Date(lastShake).toISOString(), new Date().getTime() - lastShake);

        if (data.type === "init") {
            ws.send(JSON.stringify({
                type: "init",
                key: watchID
            }));
        } else if (data.type === "shake") {
            if (new Date().getTime() - lastShake > 250) {
                console.log("Accepted");
                shakeHandler();
                lastShake = new Date().getTime();
            } else {
                console.log("Ignored");
            }
        } else if (data.type === "accepted") {
            shakeConnectionHandler();
        } else if (data.type === "disconnect") {
            location.reload();
        } else {
            ws.close();
            shakeErrorHandler();
        }

        console.log("---------------");
    }

    ws.onopen = (event) => {
        console.log(event);
    }

    ws.onclose = (event) => {
        console.log(event);
        shakeErrorHandler();
    }
}