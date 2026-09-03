<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;

use App\Http\Requests\StoreMessageRequest;
use App\Http\Requests\IndexMessageRequest;
use App\Http\Requests\UpdateMessageRequest;

use App\Http\Resources\MessageResource;


class MessageController extends Controller
{
    public function index(IndexMessageRequest $request)
    {
        $perPage = $request->input('per_page', 20);

        $query = Message::query()
            ->with('user');

        $query->orderBy(
            $request->validated('sort', 'created_at'),
            $request->validated('direction', 'desc')
        );

        $query->when(
            $request->validated('type'),
            function ($query) use ($request) {
                $query->where('type', $request->validated('type'));
            }
        );

        $query->when(
            $request->validated('user_id'),
            function ($query) use ($request) {
                $query->where('user_id', $request->validated('user_id'));
            }
        );

        $query->when(
            $request->validated('search'),
            function ($query) use ($request) {
                $query->where(
                    'text',
                    'like',
                    "%{$request->validated('search')}%"
                );
            }
        );

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
