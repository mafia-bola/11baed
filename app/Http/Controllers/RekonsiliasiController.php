<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\SatuanKerja;
use App\Rekening;
use App\LPJ;
use App\Transaksi;
use DB;

class RekonsiliasiController extends Controller
{
    private $template = [
        'title' => 'Rekonsiliasi',
        'route' => 'admin.rekonsiliasi',
        'menu' => 'rekonsiliasi',
        'icon' => 'fa fa-handshake-o',
        'theme' => 'skin-red'
    ];

    private $kategori = [
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

    public function index(Request $request)
    {
        $template = (object) $this->template;
        $satker = [];
        $rekening = [];
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
        $data = [];
        $satker = SatuanKerja::all();
        
        if($request->has('satker')){
            $rekening = Rekening::where('satker_id',$request->satker)
                ->get();
        };
        if($request->has('satker') && !empty($request->satker) 
            && $request->has('rekening') && !empty($request->rekening)
            && $request->has('tahun') && !empty($request->tahun)
            && $request->has('bulan') && !empty($request->bulan)){

            $lpj = LPJ::where('rekening_id',$request->rekening)
                ->where('tahun',$request->tahun)
                ->where('bulan',$request->bulan)
                ->orderBy('id','desc')
                ->first();
            if($lpj != null){
                $transaksi = Transaksi::select(
                    DB::raw("
                        SUM(CASE WHEN tipe = 'Pemasukan' THEN nominal ELSE 0 END) as total_pemasukan,
                        SUM(CASE WHEN tipe = 'Pengeluaran' THEN nominal ELSE 0 END) as total_pengeluaran
                    "),
                    'kategori'
                )
                ->where('rekening_id',$request->rekening)
                ->whereYear('tgl_transaksi',$request->tahun)
                ->whereMonth('tgl_transaksi',$this->convertBulan($request->bulan))
                ->groupBy('kategori')
                ->get();
                foreach($this->kategori as $kategori){
                    $data[$kategori['name']] = [
                        'hasil_transaksi' => 0,
                        'hasil_lpj' => $lpj->{$this->convertKategori($kategori['name'])},
                        'status' => 'TIDAK SESUAI'
                    ];
                }
                foreach($transaksi as $item){
                    $total =  $item->total_pemasukan - $item->total_pengeluaran;
                    $data[$item->kategori]['hasil_transaksi'] = $total;
                    $data[$item->kategori]['status'] = $total == $data[$item->kategori]['hasil_lpj'] ? 'SESUAI' : 'TIDAK SESUAI';
                }
            }
            
        }
        $kategori = $this->kategori;
        return view('admin.rekonsiliasi.index',compact('template','satker','rekening','bln','data','kategori','lpj'));
    }

    protected function convertBulan($bulan){
        $bln = [
            'Januari' => 1,
            'Februari' => 2,
            'Maret' => 3,
            'April' => 4,
            'Mei' => 5,
            'Juni' => 6,
            'Juli' => 7,
            'Agustus' => 8,
            'September' => 9,
            'Oktober' => 10,
            'November' => 11,
            'Desember' => 12
        ];
        return $bln[$bulan];
    }

    protected function convertKategori($k){
        return str_slug(strtolower($k),'_');
    }
}
