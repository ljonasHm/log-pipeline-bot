<?php

namespace App\Http\Controllers;

use App\Events\MessageCreated;
use App\Http\Requests\IndexMessageRequest;
use App\Http\Requests\StoreMessageRequest;
use App\Http\Requests\UpdateMessageRequest;
use App\Http\Resources\MessageResource;
use App\Models\Message;
use App\Models\MessageType;
use App\Models\Server;

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

    public function show(Message $message): MessageResource
    {
        $message->load('server');

        return new MessageResource($message);
    }

    public function store(StoreMessageRequest $request)
    {

        /** @var Server $server */
        $server = $request->attributes->get('server');

        $type = $request->validated('type');

        $message = $server->messages()->create([
            'text' => $request->validated('text'),
            'type' => $type,
            'message_type_id' => MessageType::query()->withName($type)->value('id'),
        ]);

        $message->load('server');

        event(new MessageCreated($message));

        return (new MessageResource($message))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateMessageRequest $request, Message $message): MessageResource
    {
        $message->update($request->validated());

        return new MessageResource($message);
    }

    public function destroy(Message $message)
    {
        $message->delete();

        return response()->noContent();
    }
}
