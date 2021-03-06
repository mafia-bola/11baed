<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\ControllerTrait;
use App\Rekening;
use App\LPJ;
use Alert;
use Auth;

class LPJController extends Controller
{
    use ControllerTrait;

    private $template = [
        'title' => 'Laporan Pertanggung Jawaban',
        'route' => 'admin.lpj',
        'menu' => 'lpj',
        'icon' => 'fa fa-book',
        'theme' => 'skin-blue',
        'config' => [
            'index.create.is_show' => 'Operator',
            'index.delete.is_show' => 'Operator',
            'index.edit.is_show' => 'Operator',
            
        ]
    ];

    private function form()
    {
        $rekening = Rekening::select('id as value','nama_rekening as name')
            ->where('satker_id',auth()->user()->satker_id)
            ->get();
        $bulan = [];
        $bln = [
            'Januari',
            'Februari',
            'Maret',
            'April',
            'Mei',
            'Juni',
            'Juli',
            'Agustus',
            'September',
            'Oktober',
            'November',
            'Desember'
        ];
        $tahun = [];
        for($i = 2018; $i < 2025; $i++){
            $tahun[] = [
                'value' => $i,
                'name' => $i
            ];
        }
        foreach($bln as $b){
            $bulan[] = [
                'value' => $b,
                'name' => $b
            ];
        }
        return [
            [
                'label' => 'Rekening',
                'name' => 'rekening_id',
                'type' => 'select',
                'option' => $rekening,
                'view_index' => true,
                'view_relation' => 'rekening->nama_rekening'
            ],
            [
                'label' => 'Bulan',
                'name' => 'bulan',
                'type' => 'select',
                'option' => $bulan,
                'view_index' => true,
            ],
            [
                'label' => 'Tahun',
                'name' => 'tahun',
                'type' => 'select',
                'option' => $tahun,
                'view_index' => true
            ],
            [
                'label' => 'Tanggal Input',
                'name' => 'tanggal_input',
                'type' => 'datepicker'
            ],
            [
                'label' => 'Tanggal Dokumen',
                'name' => 'tanggal_dokumen',
                'type' => 'datepicker'
            ],
            [
                'label' => 'No Dokumen',
                'name' => 'no_dokumen',
                'type' => 'text'
            ],
            [
                'label' => 'Saldo Awal',
                'name' => 'saldo_awal',
                'value' => 1,
                'type' => 'hidden',
                'format' => 'rupiah'
            ],
            [
                'label' => 'BP Kas',
                'name' => 'bp_kas',
                'type' => 'text',
                'validation.store' => 'required|numeric',
                'validation.update' => 'required|numeric',
                'format' => 'rupiah'
            ],
            [
                'label' => 'BP Uang',
                'name' => 'bp_uang',
                'type' => 'text',
                'validation.store' => 'required|numeric',
                'validation.update' => 'required|numeric',
                'format' => 'rupiah'
            ],
            [
                'label' => 'BP BPP',
                'name' => 'bp_bpp',
                'type' => 'text',
                'validation.store' => 'required|numeric',
                'validation.update' => 'required|numeric',
                'format' => 'rupiah'
            ],
            [
                'label' => 'BP UP',
                'name' => 'bp_up',
                'type' => 'text',
                'validation.store' => 'required|numeric',
                'validation.update' => 'required|numeric',
                'format' => 'rupiah'
            ],
            [
                'label' => 'BP LS Bendahara',
                'name' => 'bp_is_bendahara',
                'type' => 'text',
                'validation.store' => 'required|numeric',
                'validation.update' => 'required|numeric',
                'format' => 'rupiah'
            ],
            [
                'label' => 'BP Pajak',
                'name' => 'bp_pajak',
                'type' => 'text',
                'validation.store' => 'required|numeric',
                'validation.update' => 'required|numeric',
                'format' => 'rupiah'
            ],
            [
                'label' => 'BP Lain Lain',
                'name' => 'bp_lain_lain',
                'type' => 'text',
                'validation.store' => 'required|numeric',
                'validation.update' => 'required|numeric',
                'format' => 'rupiah'
            ],
            
            [
                'label' => 'Saldo',
                'name' => 'saldo',
                'type' => 'text',
                'validation.store' => 'required|numeric',
                'validation.update' => 'required|numeric',
                'format' => 'rupiah'
            ],
            [
                'label' => 'File',
                'name' => 'file',
                'type' => 'file',
            ],
        ];
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $template = (object) $this->template;
        $form = $this->form();
        if(Auth::guard()->user()->role == "Admin"){
            $data = LPJ::all();
        }else{
            $data = LPJ::join('rekening','rekening.id','=','lpj.rekening_id')
            ->join('satker','satker.id','=','rekening.satker_id')
            ->where('satker.id',auth()->user()->satker_id)->get();
        }
        return view('admin.master.index',compact('template','form','data'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $template = (object) $this->template;
        $form = $this->form();
        $data = LPJ::all();
        return view('admin.master.create',compact('template','form','data'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->formValidation($request);
        
        $lpj = LPJ::where('rekening_id',$request->rekening_id)
            ->orderBy('id','desc')
            ->first();
            // dd($lpj);
        
        $data = $request->all();
        $data['saldo_awal'] = $lpj == null ? 0 : $lpj->saldo;
        $this->uploadFile($request,$data);
        LPJ::create($data);
        Alert::make('success','Berhasil simpan data');
        return redirect(route($this->template['route'].'.index'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $template = (object) $this->template;
        $form = $this->form();
        $data = LPJ::find($id);
        return view('admin.master.show',compact('template','form','data'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $template = (object) $this->template;
        $form = $this->form();
        $data = LPJ::find($id);
        return view('admin.master.edit',compact('template','form','data'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->formValidation($request);
        $data = $request->all();
        $this->uploadFile($request,$data);
        LPJ::find($id)
            ->update($data);
        Alert::make('success','Berhasil simpan data');
        return redirect(route($this->template['route'].'.index'));    
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        LPJ::find($id)
            ->delete();
        Alert::make('success','Berhasil menghapus data');
        return redirect(route($this->template['route'].'.index'));
    }
}
