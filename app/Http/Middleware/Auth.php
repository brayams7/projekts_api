<?php

namespace App\Http\Middleware;

use App\Models\CustomResponse;
use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth as FacadesAuth;
use Tymon\JWTAuth\Facades\JWTAuth;

class Auth
{

    public function handle(Request $request, Closure $next)
    {
        $authorizationHeader = $request->header('Authorization');

        if (!$authorizationHeader) {
            $r = CustomResponse::unAuthorized("NO autorizado");
            return response()->json($r, $r->code);
        }

        try {


            $user = JWTAuth::parseToken()->authenticate(); // recibe el token de los headers y autentica al usuario.

            $role = $user->role;
            $permissions =  $role->permissions()->get();

            if( !$permissions){
                $r = CustomResponse::forbidden("Usuario/Rol sin permisos");
                return response()->json([$r],$r->code);
            }

            $request['user'] = $user;

        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            $r = CustomResponse::unAuthorized("Token invalido");
            return response()->json($r, $r->code);

        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {

            $r = CustomResponse::unAuthorized("Token expirado");
            return response()->json($r, $r->code);
        } catch (Exception $e) {
            $r = CustomResponse::unAuthorized("No autorizado");
            return response()->json($r, $r->code);
        }

        return $next($request);
    }

    /**
     * Metodo para refrescar el token al momento de que el token expire.
     *
     */

    // public function handle(Request $request, Closure $next)
    // {

    //     try {
    //         $authorizationHeader = $request->header('Authorization');
    //         if (!$authorizationHeader) {
    //             // El encabezado "Authorization" no está presente en la solicitud
    //             $r = CustomResponse::unAuthorized("NO autorizado");
    //             return response()->json($r);
    //         }

    //         $user = JWTAuth::parseToken()->authenticate(); // recibe el token de los headers y autentica al usuario.

    //         $token = JWTAuth::parseToken()->getToken();
    //         $role = $user->role;
    //         $permissions =  $role->permissions()->get();

    //         if( !$permissions){
    //             $r = CustomResponse::forbidden("Usuario/Rol sin permisos");
    //             return response()->json([$r]);
    //         }

    //         return response()->json(
    //             [
    //                 'data'=>$user,
    //                 'token'=>$token,
    //                 'auto'=>$authorizationHeader,
    //                 'message' => 'Token invalido',
    //                 'status'=>200
    //             ]
    //         );
    //     } catch (Exception $e) {
    //         if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException){
    //             $r = CustomResponse::unAuthorized("Token invalido");
    //             return response()->json($r);

    //         }else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException){
    //             try {
    //                 // Intentamos obtener un nuevo token de acceso usando el "refresh token"
    //                 $newToken = JWTAuth::refresh();

    //                 // Devolvemos la respuesta con el nuevo token y el "refresh token"
    //                 return response()->json([
    //                     'data' => '',
    //                     'token' => $newToken,
    //                     'refresh_token' => '', // Aquí debes incluir el "refresh token" correspondiente al usuario autenticado
    //                     'message' => 'Token expirado, se ha generado un nuevo token',
    //                     'status' => 200
    //                 ]);
    //             } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
    //                 // No se pudo generar un nuevo token (por ejemplo, el "refresh token" ha expirado o es inválido)
    //                 return response()->json([
    //                     'data' => '',
    //                     'message' => 'No autorizado desde el refresh token',
    //                     'status' => 400
    //                 ]);
    //             }
    //             // $r = CustomResponse::unAuthorized("Token expirado");
    //             // return response()->json($r);
    //         }else{
    //             $r = CustomResponse::unAuthorized("No autorizado");
    //             return response()->json($r);
    //         }
    //     }

    //     return $next($request);
    // }
}
