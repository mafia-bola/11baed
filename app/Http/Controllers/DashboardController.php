<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Carbon\Carbon;
use App\SatuanKerja;
class DashboardController extends Controller
{

    private $template = [
        'title' => 'Dashboard',
        'route' => 'dashboard',
        'menu' => 'dashboard',
        'icon' => 'fa fa-home',
        'theme' => 'skin-blue'
    ]; 

    public function index(Request $request)
    {   
        $data = [];
        $satker = SatuanKerja::all();
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
        $tahun = $request->has('tahun') ? $request->tahun : 2016;
        $bulan = $request->has('bulan') ? $request->bulan : 1;
        $tahunan = $request->has('tahunan');

        $pp_tahun = $request->has('pp_tahun') ? $request->pp_tahun : 2016;
        $pp_bulan = $request->has('pp_bulan') ? $request->pp_bulan : 1;
        $pp_tahunan = $request->has('pp_tahunan');

        $tr_tahun = $request->has('tr_tahun') ? $request->tr_tahun : 2016;
        $tr_bulan = $request->has('tr_bulan') ? $request->tr_bulan : 1;
        $tr_tahunan = $request->has('tr_tahunan');

        $transaksi_status = [
            [
                'value' => 'Sukses',
                'name' => 'Sukses',
            ],
            [
                'value' => 'Gagal',
                'name' => 'Gagal',
            ],
            [
                'value' => 'Retur',
                'name' => 'Retur',
            ]
        ];

        if(auth()->user()->satker_id == null){
            $data = DB::table('spm')
                ->select(DB::raw('
                    satker.nama_satker,
                    SUM(CASE WHEN spm.status = "Diterima" THEN 1 ELSE 0 END) AS diterima,
                    SUM(CASE WHEN spm.status = "Pending" THEN 1 ELSE 0 END) AS pending,
                    SUM(CASE WHEN spm.status = "Ditolak" THEN 1 ELSE 0 END) as ditolak
                '))
                ->join('rekening','rekening.id','=','spm.rekening_id')
                ->join('satker','satker.id','=','rekening.satker_id')
                ->groupBy('satker.id')
                ->whereRaw("YEAR(spm.tanggal_surat) = $tahun");
            if(!$tahunan){
                $data->whereRaw("MONTH(spm.tanggal_surat) = $bulan");
            }
                $data = $data->get();
            
            $pp = DB::table('transaksi')
                ->select(DB::raw('
                    satker.nama_satker,
                    SUM(CASE WHEN transaksi.tipe = "Pemasukan" THEN transaksi.nominal ELSE 0 END) AS pemasukan,
                    SUM(CASE WHEN transaksi.tipe = "Pengeluaran" THEN transaksi.nominal ELSE 0 END) AS pengeluaran
                '))
                ->join('rekening','rekening.id','=','transaksi.rekening_id')
                ->join('satker','satker.id','=','rekening.satker_id')
                ->groupBy('satker.id')
                ->whereRaw("YEAR(transaksi.tgl_transaksi) = $pp_tahun");
                if(!$pp_tahunan){
                    $pp->whereRaw("MONTH(transaksi.tgl_transaksi) = $pp_bulan");
                }
            $pp = $pp->get();

            $tr = DB::table('transaksi')
                ->select(DB::raw('
                    satker.nama_satker,
                    SUM(CASE WHEN transaksi.status = "Sukses" THEN transaksi.nominal ELSE 0 END) AS sukses,
                    SUM(CASE WHEN transaksi.status = "Gagal" THEN transaksi.nominal ELSE 0 END) AS gagal,
                    SUM(CASE WHEN transaksi.status = "Retur" THEN transaksi.nominal ELSE 0 END) AS retur
                '))
                ->join('rekening','rekening.id','=','transaksi.rekening_id')
                ->join('satker','satker.id','=','rekening.satker_id')
                ->groupBy('satker.id')
                ->whereRaw("YEAR(transaksi.tgl_transaksi) = $tr_tahun");
            if(!$tr_tahunan){
                $tr->whereRaw("MONTH(transaksi.tgl_transaksi) = $tr_bulan");
            }
            $tr = $tr->get();
        }else{
            $data = DB::table('spm')
                ->select(DB::raw('
                    satker.nama_satker,
                    SUM(CASE WHEN spm.status = "Diterima" THEN 1 ELSE 0 END) AS diterima,
                    SUM(CASE WHEN spm.status = "Pending" THEN 1 ELSE 0 END) AS pending,
                    SUM(CASE WHEN spm.status = "Ditolak" THEN 1 ELSE 0 END) as ditolak
                '))
                ->join('rekening','rekening.id','=','spm.rekening_id')
                ->join('satker','satker.id','=','rekening.satker_id')
                ->groupBy('satker.id')
                ->where('satker.id',auth()->user()->satker_id)
                ->whereRaw("YEAR(spm.tanggal_surat) = $tahun");
            if(!$tahunan){
                $data->whereRaw("MONTH(spm.tanggal_surat) = $bulan");
            }
               $data = $data->get();

               $pp = DB::table('transaksi')
               ->select(DB::raw('
                   satker.nama_satker,
                   SUM(CASE WHEN transaksi.tipe = "Pemasukan" THEN transaksi.nominal ELSE 0 END) AS pemasukan,
                   SUM(CASE WHEN transaksi.tipe = "Pengeluaran" THEN transaksi.nominal ELSE 0 END) AS pengeluaran
               '))
               ->join('rekening','rekening.id','=','transaksi.rekening_id')
               ->join('satker','satker.id','=','rekening.satker_id')
               ->groupBy('satker.id')
               ->where('satker.id',auth()->user()->satker_id)
               ->whereRaw("YEAR(transaksi.tgl_transaksi) = $pp_tahun");
               if(!$pp_tahunan){
                   $pp->whereRaw("MONTH(transaksi.tgl_transaksi) = $pp_bulan");
               }
               $pp = $pp->get();

               $tr = DB::table('transaksi')
                ->select(DB::raw('
                    satker.nama_satker,
                    SUM(CASE WHEN transaksi.status = "Sukses" THEN transaksi.nominal ELSE 0 END) AS sukses,
                    SUM(CASE WHEN transaksi.status = "Gagal" THEN transaksi.nominal ELSE 0 END) AS gagal,
                    SUM(CASE WHEN transaksi.status = "Retur" THEN transaksi.nominal ELSE 0 END) AS retur
                '))
                ->join('rekening','rekening.id','=','transaksi.rekening_id')
                ->join('satker','satker.id','=','rekening.satker_id')
                ->groupBy('satker.id')
                ->where('satker.id',auth()->user()->satker_id)
                ->whereRaw("YEAR(transaksi.tgl_transaksi) = $tr_tahun");
            if(!$tr_tahunan){
                $tr->whereRaw("MONTH(transaksi.tgl_transaksi) = $tr_bulan");
            }
            $tr = $tr->get();
        }
        //  dd($pp);

        $template = (object) $this->template;
        return view('admin.dashboard.index',compact('template','data','satker','bln','pp','tr'));
    }
}
