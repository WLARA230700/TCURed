<?php

namespace App\Http\Controllers;

use App\Models\Derecho;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class DerechosController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('admin.admin_agregar_derecho');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request -> validate([
            'tipo_derecho' => 'required',
            'titulo' => 'required',
            'descripcion' => 'required',
            'archivo' => 'required'
        ]);

        $data = $request -> all();
        $file = $request -> file('archivo');

        $data['id_admin'] = 1;
        $timestamps = date('Y-m-d H:i:s');
        //$data['created_at'] = time();
        //$data['updated_at'] = time();

        $filename = 'derecho-'.$data['tipo_derecho'].time().'.'.$file -> getClientOriginalExtension();
        $data['archivo'] = $filename;

        $path = $file -> storeAs('public/derechos', $filename);

        //Derecho::create($data);

        DB::table("derechos")->insert([
            'tipo_derecho' => $data['tipo_derecho'],
            'titulo' => $data['titulo'],
            'descripcion' => $data['descripcion'],
            'archivo' => $data['archivo'],
            'id_admin' => $data['id_admin'],
            'created_at' => $timestamps,
            'updated_at' => $timestamps
        ]);

        return redirect("dashboard");
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Derecho  $derecho
     * @return \Illuminate\Http\Response
     */
    public function show(Derecho $derecho)
    {
        //
    }

    public function showDerechos(){
        $derechos = DB::select('SELECT * FROM derechos ORDER BY created_at DESC');
        return view('admin.admin_dashboard', compact('derechos'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Derecho  $derecho
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $derecho = DB::select('SELECT * FROM derechos WHERE id = '.$id);
        return view('admin.admin_modificar_derecho', compact('derecho'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Derecho  $derecho
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $request -> validate([
            'tipo_derecho' => 'required',
            'titulo' => 'required',
            'descripcion' => 'required'
        ]);

        $data = $request -> all();
        $file = $request -> file('archivo');

        $derecho = DB::select('SELECT * FROM derechos WHERE id = '.$data['id']);

        $data['id_admin'] = 1;
        $timestamps = date('Y-m-d H:i:s');

        if ($file != null) {

            $data['archivo'] = $derecho[0]->archivo;

            $path = $file->storeAs('public/derechos', $data['archivo']);

            /*$filename = 'derecho-'.$data['tipo_derecho'].time().'.'.$file -> getClientOriginalExtension();
            $data['archivo'] = $filename;

            $path = $file->storeAs('public/derechos', $filename);*/

            DB::table('derechos')->where('id', $data['id'])->update([
                'tipo_derecho' => $data['tipo_derecho'],
                'titulo' => $data['titulo'],
                'descripcion' => $data['descripcion'],
                'archivo' => $data['archivo'],
                'id_admin' => $data['id_admin'],
                'updated_at' => $timestamps
            ]);


        }else{

            DB::table('derechos')->where('id', $data['id'])->update([
                'tipo_derecho' => $data['tipo_derecho'],
                'titulo' => $data['titulo'],
                'descripcion' => $data['descripcion'],
                'id_admin' => $data['id_admin'],
                'updated_at' => $timestamps
            ]);
        }

        return redirect("dashboard");
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Derecho  $derecho
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {       
        DB::table('derechos')->whereId($id)->delete();

        return redirect('dashboard');
    }
}