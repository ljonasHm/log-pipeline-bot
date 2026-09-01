<?php

namespace App\Http\Controllers;

use App\Models\Message;

use App\Http\Requests\StoreMessageRequest;
use App\Http\Requests\IndexMessageRequest;
use App\Http\Requests\UpdateMessageRequest;

use App\Http\Resources\MessageResource;


class MessageController extends Controller
{
    public function index(IndexMessageRequest $request)
    {
        $perPage = $request->input('per_page', 20);

        $messages = Message::query()
            ->orderBy('created_at', 'desc')
            ->paginate(
                perPage: $perPage,
            );

        return MessageResource::collection($messages);
    }

    public function show(Message $message): MessageResource {
        return new MessageResource($message);
    }

    public function store(StoreMessageRequest $request) {

        $message = Message::create($request->validated());

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
