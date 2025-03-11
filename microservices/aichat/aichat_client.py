import grpc
import aichat_pb2
import aichat_pb2_grpc

def run():
    model = "gpt2" # adjust model as needed (e.g. "google/gemma-2b-it").
    with grpc.insecure_channel('localhost:50051') as channel:
        stub = aichat_pb2_grpc.AiChatStub(channel)

        while True:
            user_input = input("Enter your message (or type 'exit' to quit): ")
            if user_input.lower() == 'exit':
                break

            request = aichat_pb2.GenerateRequest(message=user_input, model=model)
            print("Server response: ", end="")
            for response in stub.generate(request):
                print(response.response, end="", flush=True)
            print() # newline for readability

if __name__ == '__main__':
    run()
