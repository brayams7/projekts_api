<?php

namespace App\Http\Controllers;

use App\Constants\Constants;
use App\Http\Requests\AttachmentRequest;
use App\Models\Attachment;
use App\Models\AttachmentType;
use App\Models\Board;
use App\Models\CustomResponse;
use App\Models\Feature;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use \Ramsey\Uuid\Uuid;
use App\Exceptions\AttachmentTypeNotFoundException;
use \Symfony\Component\HttpFoundation\BinaryFileResponse;
use \Symfony\Component\HttpFoundation\StreamedResponse;

class AttachmentController extends Controller
{

    public function createAttachment(AttachmentRequest $request):JsonResponse{
        try {
            $this->authorize("create",Attachment::class);

            if(!$request->hasFile("file")){
                $r = CustomResponse::badRequest("Agregar el archivo");
                return response()->json($r, $r->code);
            }

            $file = $request->file('file');


            $attachment = self::create($file);

            if(!$attachment){
                $r = CustomResponse::badRequest("Ocurrio un error en el servidor");
                return response()->json($r, $r->code);
            }

            $r = CustomResponse::ok($attachment);
            return response()->json($r);

        }catch (AuthorizationException $e){

            $r = CustomResponse::forbidden("No autorizado");
            return response()->json($r, $r->code);

        }catch (AttachmentTypeNotFoundException $e) {
            $r = CustomResponse::badRequest("Tipo de archivo no aceptado");
            return response()->json($r, $r->code);
        }catch (\Exception $e) {
            echo $e;
            $r = CustomResponse::intertalServerError("Ocurrió un error en el servidor");
            return response()->json($r, $r->code);
        }
    }

    public function downloadAttachment($attachmentId): BinaryFileResponse|JsonResponse
    {
        $attachment = Attachment::where('id',$attachmentId)
            ->first();

        if(!$attachment){
            $r = CustomResponse::badRequest("No existe");
            return response()->json($r, $r->code);
        }


        //$filename = pathinfo($attachment->url);
        //$path = $attachment->url;
        $path = public_path(Constants::BASE_DIRECTORY . $attachment->url);

        if (!file_exists($path)) {
            $r = CustomResponse::badRequest("El archivo no existe");
            return response()->json($r, $r->code);
        }
        $filename = pathinfo($attachment->url, PATHINFO_BASENAME);
        //echo $path;
        return response()->download($path,$filename);
    }

    public function downloadAttachmentInAWS($attachmentId): StreamedResponse|JsonResponse
    {
        $attachment = Attachment::where('id', $attachmentId)
            ->first();

        if (!$attachment) {
            $r = CustomResponse::badRequest("No existe");
            return response()->json($r, $r->code);
        }

        $filename = pathinfo($attachment->url, PATHINFO_BASENAME);
        $path = Constants::NAME_DIRECTORY . $filename;

        if (!Storage::disk(Constants::NAME_STORAGE_CLOUD)->exists($path)) {
            $r = CustomResponse::badRequest("El archivo no existe");
            return response()->json($r, $r->code);
        }

        return Storage::disk(Constants::NAME_STORAGE_CLOUD)->download($path, $filename);
}

    /**
     * @throws AttachmentTypeNotFoundException
     */
    public function create(UploadedFile $file){

        $originalName = $this->getOriginalName($file);

        $fileName = Constants::NAME_DIRECTORY. $originalName. '.' . time() . '.' . $file->getClientOriginalExtension();
        $mime = $file->getClientMimeType();

        $attachmentType = AttachmentType::where('mimetype', $mime)->first();

        if (!$attachmentType) {
            throw new AttachmentTypeNotFoundException("No se encontró el tipo de adjunto."); // Lanza una excepción personalizada
        }

        $uuid = Uuid::uuid4()->toString();

        Storage::disk(Constants::NAME_STORAGE)->put($fileName, file_get_contents($file));

        //$path = env('URL_BASE_BUCKET').$fileName;

        return Attachment::create([
            'url' => $fileName,
            'uuid' => $uuid,
            'created_at' => strtotime('now'),
            'attachment_type_id' => $attachmentType->id
        ]);
    }

    /**
     * @throws AttachmentTypeNotFoundException
     */
    public function createToAWS(UploadedFile $file){

        $originalName = $this->getOriginalName($file);

        $fileName = Constants::NAME_DIRECTORY . $originalName. '.' . time() . '.' . $file->getClientOriginalExtension();
        $mime = $file->getClientMimeType();

        $attachmentType = AttachmentType::where('mimetype', $mime)->first();

        if (!$attachmentType) {
            throw new AttachmentTypeNotFoundException("No se encontró el tipo de adjunto."); // Lanza una excepción personalizada
        }

        $uuid = Uuid::uuid4()->toString();

        $isCreated = Storage::disk(Constants::NAME_STORAGE_CLOUD)->put($fileName, file_get_contents($file), 'public');

        if ($isCreated) {
            $path = env('URL_BASE_BUCKET') . $fileName;

            return Attachment::create([
                'url' => $path,
                'uuid' => $uuid,
                'created_at' => strtotime('now'),
                'attachment_type_id' => $attachmentType->id
            ]);
        }

        return null;
    }

    public function delete(string $filename):bool{
        return Storage::disk(Constants::NAME_STORAGE)->delete($filename);
    }

    protected function getOriginalName(UploadedFile $file): array|string
    {
        return pathinfo($file->getClientOriginalName(),PATHINFO_FILENAME);
    }
}
