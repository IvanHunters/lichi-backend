<?php
namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use JWTAuth;
use Hash;
use App\User;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Models\MediaLib;
use Illuminate\Support\Facades\Storage;
use Image;
use File;
use Response;

class MediaController extends Controller
{

    /**
    *
    * @OA\Get(
    *     path="/api/methods/media",
    *     summary="Вывести все файлы пользователя",
    *     tags={"Media"},
    *     @OA\Response(response="200", description="Выводит файлы пользователя"),
    *     security={{"apiAuth": {}}}
    * ),
    * @OA\Get(
    *     path="/api/methods/media/{id}",
    *     summary="Вывести файл пользователя",
    *     tags={"Media"},
    *     @OA\Parameter(
    *         name="id",
    *         in="path",
    *         description="id файла пользователя",
    *         required=true,
    *         @OA\Schema(
    *             type="string",
    *             format="string64"
    *         )
    *     ),
    *     @OA\Response(response="200", description="Выводит файл из медиабиблиотеки пользователя")
    * ),
    * @OA\Post(
    *     path="/api/methods/media",
    *     summary="Добавить файл в медиабиблиотеку пользователя",
    *     tags={"Media"},
    *     @OA\RequestBody(
    *         required=true,
    *         @OA\MediaType(
    *             mediaType="multipart/form-data",
    *             @OA\Schema(
    *                 @OA\Property(
    *                     description="Файл для загрузки",
    *                     property="file",
    *                     type="file",
    *                     format="file",
    *                 ),
    *                 required={"file"}
    *             )
    *         )
    *     ),
    *     @OA\Response(response="200", description="Добавляет файл в медиабиблиотеку пользователя"),
    *     security={{"apiAuth": {}}}
    * )
    * @OA\Put(
    *     path="/api/methods/media/{id}",
    *     summary="изменить имя файла в медиабиблиотеке",
    *     tags={"Media"},
    *     @OA\Parameter(
    *         name="id",
    *         in="path",
    *         description="id файла пользователя",
    *         required=true,
    *         @OA\Schema(
    *             type="integer",
    *             format="int64"
    *         ),
    *     ),
    *     @OA\Parameter(
    *         name="name",
    *         in="query",
    *         description="новое имя файла пользователя",
    *         required=true,
    *         @OA\Schema(
    *             type="string",
    *             format="string32"
    *         )
    *     ),
    *     @OA\Response(response="200", description="Добавляет файл в медиабиблиотеку пользователя"),
    *     security={{"apiAuth": {}}}
    * )
    * @OA\Delete(
    *     path="/api/methods/media/{id}",
    *     summary="Удалить файл из медиабиблиотеки пользователя",
    *     tags={"Media"},
    *     @OA\Parameter(
    *         name="id",
    *         in="path",
    *         description="id файла для удаления",
    *         required=true,
    *         @OA\Schema(
    *            type="string"
    *         )
    *     ),
    *     @OA\Response(response="200", description="Удаляет файл из медиабиблиотеки пользователя"),
    *     security={{"apiAuth": {}}}
    * )
    */
    public function __construct()
    {
        $this->data = [
          'status' => false,
          'code' => 401,
          'data' => null,
          'err' => [
            'code' => 1,
            'message' => 'Unauthorized'
          ]
        ];
        $this->user = auth()->user();
    }

    public function get($id, $filename)
    {

      if(Storage::disk('media')->has("{$id}/{$filename}")){
        $path = storage_path("app/media/{$id}/{$filename}");
        $file = File::get($path);
        $type = File::mimeType($path);
        $response = Response::make($file, 200);
        $response->header("Content-Type", $type);
        return $response;
      }else{
        $response = ['status'=>'error', 'code'=>'FILE_NOT_FOUND', 'message'=>['en'=>'File not found', 'ru'=>'Не найден файл']];
        return response()->json($response, 500);
      }
    }

    public function getList()
    {

      $media = MediaLib::where('creator_id',$this->user->id)->select(['id', 'name', 'path', 'path_preview', 'type'])->get();

      $response = ['status'=>'ok', 'code'=>'SUCCESS', 'items'=>$media];
      return response()->json($response);
    }

    public function create(Request $req)
    {
      if($req->hasFile('file'))
      {
        $file = $file_preview = $req->file('file');
        $filename = md5(microtime(true));

        $filename_prev = "{$filename}-preview" . '.' . $file->getClientOriginalExtension();
        $filename = $filename . '.' . $file->getClientOriginalExtension();
        $original_name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

        $images = ['img', 'jpg', 'jpeg', 'png'];
        $docs = ['gif', 'bmp', 'word', 'pdf', 'word', 'docx', 'excel', 'xlsx', 'xls', 'pptx', 'txt'];

        $MbSizeFile = $file->getSize()/1024/1024;

        if(in_array($file->getClientOriginalExtension(), $images))
        {
          $type = 1;
        }
        elseif(in_array($file->getClientOriginalExtension(), $docs))
        {
          $type = 2;
        }
        else
        {
          $response = ['status'=>'error', 'code'=>'FILE_NOT_SUPPORT', 'message'=>['en'=>'Not supported extension file', 'ru'=>'Не поддерживаемое расширение файла']];
          return response()->json($response, 500);
        }

        Storage::disk('media')->makeDirectory($this->user->id);
        Storage::disk('media')->put("{$this->user->id}/{$filename}", File::get($file));

        $full_path = $full_path_preview = "/media/{$this->user->id}/{$filename}";

        if($type == "1")
        {
          $origin_size = getimagesize($file_preview);
          if($origin_size[0] >= 300)
          {
            $file_preview = Image::make($file_preview)->resize(300, null, function ($constraint)
            {
            $constraint->aspectRatio();
            });

            $file_preview = $file_preview->save();
            Storage::disk('media')->put("{$this->user->id}/{$filename_prev}", $file_preview);

            $full_path_preview = "/media/{$this->user->id}/{$filename_prev}";
          }
        }

        $create_data = ['name'=>$original_name,
                        'path'=>$full_path,
                        'path_preview'=>$full_path_preview,
                        'type'=>$type,
                        'creator_id'=>$this->user->id
                        ];
        $data = MediaLib::create($create_data);

        $response = ['status'=>'ok', 'code'=>'SUCCESS', 'items'=>$data, 'message'=>['en'=>'The media file was successfully added', 'ru'=>'Медиа файл был успешно добавлен']];
        return response()->json($response);
      }
      else
      {
        $response = ['status'=>'error', 'code'=>'FILE_NOT_FOUND', 'message'=>['en'=>'File not found', 'ru'=>'Файл не найден']];
        return response()->json($response, 500);
      }
    }

    public function update($id, Request $req)
    {
      $data = MediaLib::where('creator_id',$this->user->id)->where('id',$id);
      if($data->count() == 0)
      {
        $response = ['status'=>'error', 'code'=>'FILE_NOT_FOUND', 'message'=>['en'=>'File not found', 'ru'=>'Файл не найден']];
        return response()->json($response, 500);
      }
      else
      {
        $data = $data->first();
        if(!$req->has('name'))
        {
          $response = ['status'=>'error', 'code'=>'NEW_NAME_NOT_FOUND', 'message'=>['en'=>'New name not found', 'ru'=>'Новое имя файла не найдено']];
          return response()->json($response, 500);
        }
        $data->name = $req->name;
        $data->save();
        $response = ['status'=>'ok', 'code'=>'SUCCESS', 'items'=>$data, 'message'=>['en'=>'The media file name was successfully updated', 'ru'=>'Имя медиафайла было успешно обновлено']];
        return response()->json($response);
      }
    }

    public function delete($id)
    {
      $data = MediaLib::where('creator_id',$this->user->id)->where('id',$id);
      if($data->count() == 0)
      {
        $response = ['status'=>'error', 'code'=>'FILE_NOT_FOUND', 'message'=>['en'=>'File not found', 'ru'=>'Файл не найден']];
        return response()->json($response, 500);
      }
      else
      {
        $data = $data->first();
        Storage::disk('media')->delete("{$this->user->id}/{$data->name}");
        $data->delete();
        $response = ['status'=>'ok', 'code'=>'SUCCESS', 'items'=>[], 'message'=>['en'=>'The media file was successfully deleted', 'ru'=>'Медиа файл был успешно удален']];
        return response()->json($response);
      }
    }
}
