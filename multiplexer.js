const net = require('net');
const express = require("express");
const cors = require("cors");
const grpc = require("@grpc/grpc-js");
const protoLoader = require("@grpc/proto-loader");

// Define internal ports
const HTTP_PORT = 8888;
const GRPC_PORT = 9999;
const LISTEN_PORT = 8080;

var PROTO_PATH = './proto/pinger.proto';
var packageDefinition = protoLoader.loadSync(
    PROTO_PATH,
    {
        keepCase: true,
        longs: String,
        enums: String,
        defaults: true,
        oneofs: true
    }
);

var pinger = grpc.loadPackageDefinition(packageDefinition).pinger;
var grpcClient = new pinger.Pinger('0.0.0.0:9999', grpc.credentials.createInsecure());

// Express app for HTTP + gRPC-Web requests
const app = express();
app.use(cors());
app.use((req, res, next) => {
    if (req.is("application/grpc-web+proto")) {
        let chunks = [];
        req.on("data", (chunk) => chunks.push(chunk));
        req.on("end", () => {
            req.rawBody = Buffer.concat(chunks);
            next();
        });
    } else {
        next();
    }
});

// gRPC-Web Proxy (Manually Forward Requests)
app.post("/pinger.Pinger/Ping", (req, res) => {
    grpcClient.Ping(req.rawBody, (error, response) => {
        if (error) {
            console.error("gRPC-Web Error:", error);
            res.status(500).end();
        } else {
            res.set("Content-Type", "application/grpc-web+proto");
            res.end(response.serializeBinary()); // Send binary gRPC-Web response
        }
    });
});

// Create HTTP Server
const httpServer = require("http").createServer(app);

// Function to detect protocol (basic HTTP check)
function detectProtocol(data) {
    return data.toString().startsWith('GET ') || data.toString().startsWith('POST ') ||
           data.toString().startsWith('HEAD ') || data.toString().startsWith('PUT ');
}

// Detect gRPC-Web requests (they use HTTP headers)
function detectGrpcWeb(data) {
    const requestStr = data.toString("utf8", 0, 100); // Read first 100 bytes
    return requestStr.includes("application/grpc-web");
}

// Create multiplexer server
const server = net.createServer((clientSocket) => {
    clientSocket.once('data', (data) => {
        if (detectGrpcWeb(data)) {
            // Handle gRPC-Web via the HTTP server
            httpServer.emit("connection", clientSocket);
        } else {
            let targetPort = detectProtocol(data) ? HTTP_PORT : GRPC_PORT;

            // Forward connection to the appropriate internal server
            const targetSocket = net.createConnection(targetPort, '127.0.0.1', () => {
                targetSocket.write(data); // Send initial data
                clientSocket.pipe(targetSocket).pipe(clientSocket);
            });

            targetSocket.on('error', (err) => {
                console.error(`Error connecting to target port ${targetPort}:`, err.message);
                clientSocket.end();
            });
        }
    });
});

server.listen(LISTEN_PORT, () => {
    console.log(`Multiplexer running on port ${LISTEN_PORT}`);
});
