import grpc
from concurrent import futures
from transformers import AutoTokenizer, AutoModelForCausalLM, TextIteratorStreamer
import aichat_pb2
import aichat_pb2_grpc
from threading import Thread

class AiChatServicer(aichat_pb2_grpc.AiChatServicer):
    def generate(self, request, context):
        model_name = request.model
        tokenizer = AutoTokenizer.from_pretrained(model_name)
        model = AutoModelForCausalLM.from_pretrained(model_name)

        streamer = TextIteratorStreamer(tokenizer, skip_prompt=True, skip_special_tokens=True)
        print("MESSAGE: " + request.message)
        inputs = tokenizer(request.message, return_tensors="pt")

        generation_kwargs = dict(inputs, streamer=streamer, max_new_tokens=1024, no_repeat_ngram_size=True) #adjust max_new_tokens as needed.

        def generate_and_stream():
            model.generate(**generation_kwargs)

        thread = Thread(target=generate_and_stream)
        thread.start()

        for new_text in streamer:
            print("streaming text: " + new_text)
            yield aichat_pb2.GenerateResponse(response=new_text)

def serve():
    server = grpc.server(futures.ThreadPoolExecutor(max_workers=10))
    aichat_pb2_grpc.add_AiChatServicer_to_server(AiChatServicer(), server)
    server.add_insecure_port('[::]:50051')
    server.start()
    print("Server started on port 50051")
    server.wait_for_termination()

if __name__ == '__main__':
    serve()