<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;

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
            ->with('user')
            ->orderBy(
                $request->validated('sort', 'created_at'),
                $request->validated('direction', 'desc')
            )
            ->ofType($request->validated('type'))
            ->forUser($request->validated('user_id'))
            ->search($request->validated('search'))
            ->orderByDesc('created_at');

        $messages = $query->paginate(
            $request->validated('per_page', 20)
        );

        return MessageResource::collection($messages);
    }

    public function show(Message $message): MessageResource {
        $message->load('user');
        return new MessageResource($message);
    }

    public function store(StoreMessageRequest $request) {

        $user = User::findOrFail($request->validated('user_id'));

        $message = $user->messages()->create([
            'text' => $request->validated('text'),
            'type' => $request->validated('type')
        ]);

        $message->load('user');

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
