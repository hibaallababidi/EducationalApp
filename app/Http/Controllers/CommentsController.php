<?php

namespace App\Http\Controllers;

use App\Http\Requests\Student\addCommentRequest;
use App\Http\Requests\Student\DisplayItemCommentsRequest;
use App\Http\Services\Responses\StudentResponse;
use App\Http\Services\StudentServices;
use Illuminate\Http\JsonResponse;
use JetBrains\PhpStorm\Pure;

class CommentsController extends Controller
{
    private StudentServices $services;
    private StudentResponse $response;

    #[Pure] public function __construct()
    {
        $this->services = new StudentServices();
        $this->response = new StudentResponse();
    }


    public function addComment(addCommentRequest $request): JsonResponse
    {
        $comment = $this->services->saveComment($request);
        return $this->response->addCommentResponse($comment);
    }

    public function displayItemComments(DisplayItemCommentsRequest $request): JsonResponse
    {
        $comments = $this->services->getItemComments($request->item_id);
        return $this->response->displayItemCommentsResponse($comments);
    }
}
