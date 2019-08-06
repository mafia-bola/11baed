<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\ControllerTrait;
use Alert;
use App\Rekening;
use App\Transaksi;
use Auth;
use Illuminate\Support\Facades\DB;
use App\SatuanKerja;
use Mpdf\Mpdf;

class TransaksiController extends Controller
{
    use ControllerTrait;

    private $template = [
        'title' => 'Transaksi',
        'route' => 'admin.transaksi',
        'menu' => 'transaksi',
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
        $satker = Rekening::select('id as value','nama_rekening as name')
            ->where('satker_id',auth()->user()->satker_id)
            ->get();
        
        $kategori = [
            [
                'value' => 'BP KAS',
                'name' => 'BP KAS'
            ],
            [ 
                'value' => 'BP UANG',
                'name' => 'BP UANG',
            ],
            [
                'value' => 'BP BPP',
                'name' => 'BP BPP',
            ],
            [
                'value' => 'BP UP',
                'name' => 'BP UP',
            ],
            [
                'value' => 'BP UANG',
                'name' => 'BP UANG',
            ],
            [
                'value' => 'BP IS BENDAHARA',
                'name' => 'BP IS BENDAHARA',
            ],
            [
                'value' => 'BP PAJAK',
                'name' => 'BP PAJAK',
            ],
            [
                'value' => 'BP LAIN LAIN',
                'name' => 'BP LAIN LAIN',
            ], 
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
                'label' => 'Tipe',
                'name' => 'tipe',
                'type' => 'select',
                'option' => [
                    [
                        'value' => 'Pengeluaran',
                        'name' => 'Pengeluaran'
                    ],
                    [
                        'value' => 'Pemasukan',
                        'name' => 'Pemasukan'
                    ]
                ],
                'view_index' => true,
            ],
            [
                'label' => 'Nama Petugas',
                'name' => 'nama_petugas',
                'view_index' => true
            ],
            [
                'label' => 'Nominal',
                'name' => 'nominal',
                'view_index' => true,
                'validation.store' => 'required|numeric',
                'validation.update' => 'required|numeric',
                'format' => 'rupiah',
            ],
            [
                'label' => 'Tanggal Transaksi',
                'name' => 'tgl_transaksi',
                'type' => 'datepicker',
                'view_index' => true
            ],
            [
                'label' => 'Metode Pembayaran',
                'name' => 'metode_pembayaran',
                'view_index' => true,
                'type' => 'select',
                'option' => [
                    [
                        'value' => 'Transfer',
                        'name' => 'Transfer'
                    ],
                    [
                        'value' => 'Cek',
                        'name' => 'Cek'
                    ]
                ],
            ],
            [
                'label' => 'Nomor Cek',
                'name' => 'no_cek',
                'view_index' => true
            ],
            [
                'label' => 'Kategori',
                'name' => 'kategori',
                'type' => 'select',
                'option' => $kategori,
                'view_index' => true
            ],
            [
                'label' => 'Status',
                'name' => 'status',
                'type' => 'select',
                'option' => [
                    [
                        'value' => 'Sukses',
                        'name' => 'Sukses'
                    ],
                    [
                        'value' => 'Retur',
                        'name' => 'Retur'
                    ],
                    [
                        'value' => 'Gagal',
                        'name' => 'Gagal'
                    ]
                ],
                'view_index' => true
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
        if(Auth::guard()->user()->role == "Admin"){
            $satker = SatuanKerja::all();
            $rekening = [];
            $tahun = [2016,2017,2018,2019];
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
            $kategori = [
                [
                    'value' => 'BP KAS',
                    'name' => 'BP KAS'
                ],
                [ 
                    'value' => 'BP UANG',
                    'name' => 'BP UANG',
                ],
                [
                    'value' => 'BP BPP',
                    'name' => 'BP BPP',
                ],
                [
                    'value' => 'BP UP',
                    'name' => 'BP UP',
                ],
                [
                    'value' => 'BP UANG',
                    'name' => 'BP UANG',
                ],
                [
                    'value' => 'BP IS BENDAHARA',
                    'name' => 'BP IS BENDAHARA',
                ],
                [
                    'value' => 'BP PAJAK',
                    'name' => 'BP PAJAK',
                ],
                [
                    'value' => 'BP LAIN LAIN',
                    'name' => 'BP LAIN LAIN',
                ], 
            ];
            if($request->has('satker')){
                $rekening = Rekening::where('satker_id',$request->satker)
                    ->get();
            }

            $tr = Transaksi::select(DB::raw('transaksi.*'))
                ->join('rekening','rekening.id','=','transaksi.rekening_id')
                ->join('satker','satker.id','=','rekening.satker_id');
            if($request->has('satker') && $request->staker != 'all'){
                $tr->where('satker.id',$request->satker);
            }
            if($request->has('rekening') && $request->rekening != 'all'){
                $tr->where('rekening.id',$request->rekening);
            }
            if($request->has('tahun') && $request->tahun != 'all'){
                $tr->whereYear('transaksi.tgl_transaksi',$request->tahun);
            }
            if($request->has('bulan') && $request->bulan != 'all'){
                $tr->whereMonth('transaksi.tgl_transaksi',$request->bulan);
            }
            if($request->has('kategori') && $request->kategori != 'all'){
                $tr->where('transaksi.kategori',$request->kategori);
            }
            if($request->has('kategori') && $request->metode != 'all'){
                $tr->where('transaksi.metode_pembayaran',$request->metode);
            }
            $data = $tr->get();
            if($request->has('download') && $request->download == 'true'){
                $view = view('pdf.transaksi',compact('template','form','data','satker','rekening','tahun','bln','kategori'))->render();
                $pdf = new Mpdf();
                $pdf->WriteHTML($view);
                return $pdf->Output('TRANSAKSI.pdf',\Mpdf\Output\Destination::INLINE);
            }else{
                return view('admin.transaksi.index',compact('template','form','data','satker','rekening','tahun','bln','kategori'));
            }
        }else{
          
            $satker = SatuanKerja::where('id',auth()->user()->satker_id)->get();
            $rekening = [];
            $tahun = [2016,2017,2018,2019];
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
            $kategori = [
                [
                    'value' => 'BP KAS',
                    'name' => 'BP KAS'
                ],
                [ 
                    'value' => 'BP UANG',
                    'name' => 'BP UANG',
                ],
                [
                    'value' => 'BP BPP',
                    'name' => 'BP BPP',
                ],
                [
                    'value' => 'BP UP',
                    'name' => 'BP UP',
                ],
                [
                    'value' => 'BP UANG',
                    'name' => 'BP UANG',
                ],
                [
                    'value' => 'BP IS BENDAHARA',
                    'name' => 'BP IS BENDAHARA',
                ],
                [
                    'value' => 'BP PAJAK',
                    'name' => 'BP PAJAK',
                ],
                [
                    'value' => 'BP LAIN LAIN',
                    'name' => 'BP LAIN LAIN',
                ], 
            ];
            if($request->has('satker')){
                $rekening = Rekening::where('satker_id',$request->satker)
                    ->get();
            }

            $tr = Transaksi::select(DB::raw('transaksi.*'))
                ->join('rekening','rekening.id','=','transaksi.rekening_id')
                ->join('satker','satker.id','=','rekening.satker_id');
            if($request->has('satker') && $request->staker != 'all'){
                $tr->where('satker.id',auth()->user()->satker_id);
            }
            if($request->has('rekening') && $request->rekening != 'all'){
                $tr->where('rekening.id',$request->rekening);
            }
            if($request->has('tahun') && $request->tahun != 'all'){
                $tr->whereYear('transaksi.tgl_transaksi',$request->tahun);
            }
            if($request->has('bulan') && $request->bulan != 'all'){
                $tr->whereMonth('transaksi.tgl_transaksi',$request->bulan);
            }
            if($request->has('kategori') && $request->kategori != 'all'){
                $tr->where('transaksi.kategori',$request->kategori);
            }
            if($request->has('kategori') && $request->metode != 'all'){
                $tr->where('transaksi.metode_pembayaran',$request->metode);
            }
            $data = $tr->get();
            if($request->has('download') && $request->download == 'true'){
                $view = view('pdf.transaksi',compact('template','form','data','satker','rekening','tahun','bln','kategori'))->render();
                $pdf = new Mpdf();
                $pdf->WriteHTML($view);
                return $pdf->Output('TRANSAKSI.pdf',\Mpdf\Output\Destination::INLINE);
            }else{
                return view('admin.transaksi.index',compact('template','form','data','satker','rekening','tahun','bln','kategori'));
            }
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
        $data = Transaksi::all();
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
        Transaksi::create($request->all());
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
        $data = Transaksi::find($id);
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
        $data = Transaksi::find($id);
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
        Transaksi::find($id)
            ->update($request->all());
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
        Transaksi::find($id)
            ->delete();
        Alert::make('success','Berhasil menghapus data');
        return redirect(route($this->template['route'].'.index'));
    }
}
