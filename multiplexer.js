const net = require('net');

// Define internal ports
const HTTP_PORT = 8888;
const GRPC_PORT = 9999;
const LISTEN_PORT = 8080;

// Function to detect protocol (basic HTTP check)
function detectHTTP(data) {
    const prefixes = ["GET ", "POST ", "PUT ", "HEAD ", "OPTIONS ", "DELETE "];
    return prefixes.some(prefix => data.toString().startsWith(prefix));
}

// Create multiplexer server
const server = net.createServer((clientSocket) => {
    clientSocket.once('data', (data) => {
        let targetPort = detectHTTP(data) ? HTTP_PORT : GRPC_PORT;

        // Forward connection to the appropriate internal server
        const targetSocket = net.createConnection(targetPort, '127.0.0.1', () => {
            targetSocket.write(data); // Send initial data
            clientSocket.pipe(targetSocket).pipe(clientSocket);
        });

        targetSocket.on('error', (err) => {
            console.error(`Error connecting to target port ${targetPort}:`, err.message);
            clientSocket.end();
        });
    });
});

server.listen(LISTEN_PORT, () => {
    console.log(`Multiplexer running on port ${LISTEN_PORT}`);
});
