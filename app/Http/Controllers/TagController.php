<?php

namespace App\Http\Controllers;

use App\Http\Requests\TagRequest;
use App\Models\CustomResponse;
use App\Models\Tag;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TagController extends Controller
{
    public function createTag(TagRequest $request):JsonResponse{
        try {
            $this->authorize("create",Tag::class);

            $name = $request->tag;
            $color = $request->color;

            $tag = Tag::create([
                'tag'=>$name,
                'color'=>$color
            ]);

            $r = CustomResponse::ok($tag);
            return response()->json($r);

        }catch (AuthorizationException $e){

            $r = CustomResponse::forbidden("No autorizado");
            return response()->json($r, $r->code);

        }catch (\Exception $e) {
            $r = CustomResponse::intertalServerError("Ocurrió un error en el servidor");
            return response()->json($r, $r->code);
        }
    }

    public function listTags(Request $request):JsonResponse{
        try {
            $this->authorize("viewAny",Tag::class);

            $tags = Tag::all();

            $r = CustomResponse::ok($tags);
            return response()->json($r);

        }catch (AuthorizationException $e){

            $r = CustomResponse::forbidden("No autorizado");
            return response()->json($r, $r->code);

        }catch (\Exception $e) {
            $r = CustomResponse::intertalServerError("Ocurrió un error en el servidor");
            return response()->json($r, $r->code);
        }
    }

    public function updateTag(TagRequest $request, $tagId):JsonResponse{
        try {
            $this->authorize("updateTag",Tag::class);

            $tag = Tag::where('id',$tagId)->first();

            if(!$tag){
                $r = CustomResponse::badRequest("La etiqueta no exite");
                return response()->json($r, $r->code);
            }

            $name = $request->tag;
            $color = $request->color;

            $tag->tag = $name;
            $tag->color = $color;

            $tag->save();

            $r = CustomResponse::ok($tag);
            return response()->json($r);

        }catch (AuthorizationException $e){

            $r = CustomResponse::forbidden("No autorizado");
            return response()->json($r, $r->code);

        }catch (\Exception $e) {
            $r = CustomResponse::intertalServerError("Ocurrió un error en el servidor");
            return response()->json($r, $r->code);
        }
    }

    public function deleteTag(Request $request, $tagId):JsonResponse{
        try {
            $this->authorize("updateTag",Tag::class);

            $tag = Tag::where('id',$tagId)->first();

            if(!$tag){
                $r = CustomResponse::badRequest("La etiqueta no exite");
                return response()->json($r, $r->code);
            }

            $tag->delete();

            $r = CustomResponse::ok("OK");
            return response()->json($r);

        }catch (AuthorizationException $e){

            $r = CustomResponse::forbidden("No autorizado");
            return response()->json($r, $r->code);

        }catch (\Exception $e) {
            $r = CustomResponse::intertalServerError("Ocurrió un error en el servidor");
            return response()->json($r, $r->code);
        }
    }
}
