<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\ControllerTrait;
use App\SPM;
use Alert;
use App\Rekening;
use App\SatuanKerja;
use Illuminate\Support\Facades\DB;
use Mpdf\Mpdf;

class SPMAdminController extends Controller
{
    use ControllerTrait;

    private $template = [
        'title' => 'SPM',
        'route' => 'admin.spm-admin',
        'menu' => 'spm-admin',
        'icon' => 'fa fa-book',
        'theme' => 'skin-blue',
        'config' => [
            'index.show.is_show' => true,
            'index.create.is_show' => false,
            'index.delete.is_show' => false,
        ]
    ];

    private function form()
    {
        $satker = Rekening::select('id as value','nama_rekening as name')
            ->get();
        $status = [
            [
                'value' => 'Pending',
                'name' => 'Pending'
            ],
            [
                'value' => 'Diterima',
                'name' => 'Diterima'
            ],
            [
                'value' => 'Ditolak',
                'name' => 'Ditolak'
            ]
        ];
        return [
            [
                'label' => 'Rekening',
                'name' => 'rekening_id',
                'type' => 'select',
                'option' => $satker,
                'view_index' => true,
                'view_relation' => 'rekening->nama_rekening'
            ],
            [
                'label' => 'Tanggal Surat',
                'name' => 'tanggal_surat',
                'type' => 'datepicker',
                'view_index' => true,
            ],
            [
                'label' => 'Nomor Surat',
                'name' => 'no_surat',
                'view_index' => true,
            ],
            [
                'label' => 'Jenis SPM',
                'name' => 'jenis_spm',
                'view_index' => true
            ],
            [
                'label' => 'Nominal',
                'name' => 'nominal',
                'validation.store' => 'required|numeric',
                'validation.update' => 'required|numeric',
                'format' => 'rupiah',
                'view_index' => true
            ],
            [
                'label' => 'Status',
                'name' => 'status',
                'type' => 'select',
                'option' => $status,
                'view_index' => true,
            ],
            [
                'label' => 'File',
                'name' => 'file',
                'type' => 'file'
            ]
        ];
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $template = (object) $this->template;
        $form = $this->form();
        $satker = SatuanKerja::all();
        $rekening = [];
        $tahun = [2016,2017,2018,2019];
        $status = [
            [
                'value' => 'Pending',
                'name' => 'Pending'
            ],
            [
                'value' => 'Diterima',
                'name' => 'Diterima'
            ],
            [
                'value' => 'Ditolak',
                'name' => 'Ditolak'
            ]
        ];
        $bln = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember'
        ];

        if($request->has('satker')){
            $rekening = Rekening::where('satker_id',$request->satker)
                ->get();
        }

        $spm = SPM::select(DB::raw('spm.*'))
            ->join('rekening','rekening.id','=','spm.rekening_id')
            ->join('satker','satker_id','=','rekening.satker_id');
        if($request->has('satker') && $request->satker != 'all'){
            $spm->where('satker.id',$request->satker);
        }
        if($request->has('rekening') && $request->rekening != 'all'){
            $spm->where('rekening.id',$request->rekening);
        }
        if($request->has('tahun') && $request->tahun != 'all'){
            $spm->whereYear('spm.tanggal_surat',$request->tahun);
        }
        if($request->has('bulan') && $request->bulan != 'all'){
            $spm->whereMonth('spm.tanggal_surat',$request->bulan);
        }
        if($request->has('status') && $request->status != 'all'){
            $spm->where('spm.status',$request->status);
        }
        $data = $spm->get();
        if($request->has('download') && $request->download == 'true'){
            $view = view('pdf.spm',compact('template','form','data','satker','rekening','tahun','bln','status'))->render();
            $pdf = new Mpdf();
            $pdf->WriteHTML($view);
            return $pdf->Output('SPM.pdf',\Mpdf\Output\Destination::INLINE);
        }else{
            return view('admin.spm.index',compact('template','form','data','satker','rekening','tahun','bln','status'));
        }
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
        $data = SPM::all();
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
        $data = $request->all();

        $this->uploadFile($request,$data);
        SPM::create($data);

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
        $data = SPM::find($id);
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
        $data = SPM::find($id);
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

        SPM::find($id)
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
        SPM::find($id)
            ->delete();
        Alert::make('success','Berhasil menghapus data');
        return redirect(route($this->template['route'].'.index'));
    }
}
