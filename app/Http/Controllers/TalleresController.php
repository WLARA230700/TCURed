<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Validator;
use Illuminate\Support\Facades\Auth;

class TalleresController extends Controller
{
    //
    public function index()
    {
        return view('admin.admin_agregar_taller');
    }

    public function showTalleres(){
        $talleres = DB::select('SELECT * FROM table_talleres ORDER BY created_at DESC');
        $tipoContenido = "TALLERES";
        return view('admin.admin_dashboard', compact('talleres', 'tipoContenido'));
    }

    public function showTalleresUser(){
        $talleres = DB::select('SELECT * FROM table_talleres');
        return view('temas_interes', compact('talleres'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make([
            'nombre' => $request['nombre'],
            'fecha' => $request['fecha'],
            'lugar' => $request['lugar'],
            'descripcion' => $request['descripcion'],
            'portada' => $request['portada']
        ],
        [
            'nombre' => 'required',
            'fecha' => 'required',
            'lugar' => 'required',
            'descripcion' => 'required',
            'portada' => 'required'
        ]);

        if (!$validator->fails()) {

            $validator = Validator::make([
                'portada' => $request['portada']
            ],
            [
                'portada' => 'image|mimes:jpeg,png,jpg'
            ]);

            if (!$validator->fails()) {
                $validator = Validator::make([
                    'portada' => $request['portada']
                ],
                [
                    'portada' => 'max:2048'
                ]);
    
                if (!$validator->fails()) {
                    $data = $request -> all();
                    $file = $request -> file('portada');
    
                    $data['id_admin'] = Auth::id();
                    $timestamps = date('Y-m-d H:i:s');
    
                    $filename = 'imagen-taller'.time().'.'.$file -> getClientOriginalExtension();
                    $data['portada'] = $filename;
    
                    //$path = $file -> storeAs('imagenes', $filename);
                    $path = "storage/talleres/";
                    $ruta = $path.$filename;

                    $_FILES['portada']['name'] = $filename;

                    $nombre=$_FILES['portada']['name'];
                    $guardado=$_FILES['portada']['tmp_name'];

                    if(move_uploaded_file($guardado, $path.$nombre)){
                        echo "Archivo guardado con exito";
                        DB::table("talleres")->insert([
                            'nombre' => $data['nombre'],
                            'fecha' => $data['fecha'],
                            'lugar' => $data['lugar'],
                            'descripcion' => $data['descripcion'],
                            'portada' => $data['portada'],
                            'id_admin' => $data['id_admin'],
                            'created_at' => $timestamps,
                            'updated_at' => $timestamps
                        ]);
                    }else{
                        echo "Archivo no se pudo guardar";
                    }    
                    return redirect("dashboard");
            
                }else{
                    return redirect("addTaller")->with('messageError', 'Seleccione una imagen menor a 2MB');
                }

            }else{
                return redirect("addTaller")->with('messageError', 'Seleccione una imagen PNG o JPG');
            }

            
        }else{
            return redirect("addTaller")->with('messageError', 'Rellene todos los campos');
        }

        
    }

    public function edit($id)
    {
        $taller = DB::select('SELECT * FROM table_talleres WHERE id = '.$id);
        return view('admin.admin_modificar_taller', compact('taller'));
    }

    public function update(Request $request)
    {
        $validator = Validator::make([
            'nombre' => $request['nombre'],
            'fecha' => $request['fecha'],
            'lugar' => $request['lugar'],
            'descripcion' => $request['descripcion']
        ],
        [
            'nombre' => 'required',
            'fecha' => 'required',
            'lugar' => 'required',
            'descripcion' => 'required'
        ]);

        if (!$validator->fails()) {

            $data = $request -> all();
            $file = $request -> file('portada');
            $data['id_admin'] = Auth::id();
            $timestamps = date('Y-m-d H:i:s');

            if ($file != null) {

                $validator = Validator::make([
                    'portada' => $request['portada']
                ],
                [
                    'portada' => 'image|mimes:jpeg,png,jpg'
                ]);

                if (!$validator->fails()) {
                    $validator = Validator::make([
                        'portada' => $request['portada']
                    ],
                    [
                        'portada' => 'max:2048'
                    ]);
        
                    if (!$validator->fails()) {
                
                        $taller = DB::select('SELECT * FROM table_talleres WHERE id = '.$data['id']);
                        
                        $data['portada'] = $taller[0]->portada;

                        $path = "storage/talleres/";
                        $ruta = $path.$taller[0]->portada;

                        $_FILES['portada']['name'] = $taller[0]->portada;

                        $nombre=$_FILES['portada']['name'];
                        $guardado=$_FILES['portada']['tmp_name'];

                        if(move_uploaded_file($guardado, $path.$nombre)){
                            echo "Archivo guardado con exito";
                            DB::table('table_talleres')->where('id', $data['id'])->update([
                                'nombre' => $data['nombre'],
                                'fecha' => $data['fecha'],
                                'lugar' => $data['lugar'],
                                'descripcion' => $data['descripcion'],
                                'portada' => $data['portada'],
                                'id_admin' => $data['id_admin'],
                                'updated_at' => $timestamps
                            ]);
                        }else{
                            echo "Archivo no se pudo guardar";
                        }
                
                        return redirect("dashboard");

                    }else{
                        return redirect("modifyTaller/".$request['id'])->with('messageError', 'La imagen debe ser menor a 2MB');
                    }
                }else{
                    return redirect("modifyTaller/".$request['id'])->with('messageError', 'La imagen debe ser formato PNG o JPG');
                }
            }else{
                DB::table('table_talleres')->where('id', $data['id'])->update([
                    'nombre' => $data['nombre'],
                    'fecha' => $data['fecha'],
                    'lugar' => $data['lugar'],
                    'descripcion' => $data['descripcion'],
                    'id_admin' => $data['id_admin'],
                    'updated_at' => $timestamps
                ]);
                return redirect("dashboard");
            }
        }else{
            return redirect("modifyTaller/".$request['id'])->with('messageError', 'Rellene los campos');
        }
    }


    public function destroy($id)
    {
        $filename = DB::select('SELECT * FROM talleres WHERE id = '.$id);
        
        $pathToFile = 'storage/talleres/'.$filename[0]->archivo;

        if(unlink($pathToFile)){
            echo "Eliminado";
        }else{
            echo "No se pudo eliminar";
        }

        DB::table('talleres')->whereId($id)->delete();

        return redirect('dashboard');
    }
}
