<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Server;

use App\Http\Requests\StoreMessageRequest;
use App\Http\Requests\IndexMessageRequest;
use App\Http\Requests\UpdateMessageRequest;
use App\Http\Resources\MessageResource;

use App\Events\MessageCreated;


class MessageController extends Controller
{
    public function index(IndexMessageRequest $request)
    {
        $perPage = $request->input('per_page', 20);

        $query = Message::query()
            ->with('server')
            ->orderBy(
                $request->validated('sort', 'created_at'),
                $request->validated('direction', 'desc')
            )
            ->ofType($request->validated('type'))
            ->forServer($request->validated('server_id'))
            ->search($request->validated('search'))
            ->orderByDesc('created_at');

        $messages = $query->paginate(
            $request->validated('per_page', 20)
        );

        return MessageResource::collection($messages);
    }

    public function show(Message $message): MessageResource {
        $message->load('server');
        return new MessageResource($message);
    }

    public function store(StoreMessageRequest $request) {

        $server = Server::findOrFail($request->validated('server_id'));

        $message = $server->messages()->create([
            'text' => $request->validated('text'),
            'type' => $request->validated('type')
        ]);

        $message->load('server');

        event(new MessageCreated($message));

        return (new MessageResource($message))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateMessageRequest $request, Message $message): MessageResource {
        $message->update($request->validated());

        return new MessageResource($message);
    }

    public function destroy(Message $message) {
        $message->delete();

        return response()->noContent();
    }
}
