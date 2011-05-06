def web_socket_do_extra_handshake(request):
    pass  # Always accept.


def web_socket_transfer_data(request):
    while True:
        line = request.ws_stream.receive_message()
        if line is None:
            return
        request.ws_stream.send_message(line)
        if line == _GOODBYE_MESSAGE:
            return
