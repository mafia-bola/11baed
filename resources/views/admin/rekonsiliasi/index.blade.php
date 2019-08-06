@extends('admin.layouts.app')
@push('css')
<style>
    .table-border th,.table-border td, .table-border thead{
        border : 1px solid #b9b9b9;
        border-bottom: 1px solid #b9b9b9 !important;
    }
    .table-border th{
        text-align: center;
    }
</style>
@endpush
@section('content')
    @php
        @$config = $template->config == null ? [] : $template->config;
    @endphp
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1>
                {{$template->title}}                
            </h1>
            <ol class="breadcrumb">
                <li><a href="{{route('admin.dashboard.index')}}"><i class="fa fa-dashboard"></i> Home</a></li>
                <li class="active">{{$template->title}}</li>
            </ol>
        </section>

        <!-- Main content -->
        <section class="content">
           <div class="row">
               <div class="col-md-12">
                    {!!Alert::showBox()!!}
                    <div class="box box-info">
                        <div class="box-header">
                            <h3 class="box-title"><i class="{{$template->icon}}"></i> {{$template->title}}</h3>
                        </div>
                        <div class="box-body">
                            <div class="row">
                                <form action="{{route('admin.rekonsiliasi.index')}}" method="GET" id="formRekon">
                                    <input type="hidden" name="download" value="false" id="download">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="">Satuan Kerja</label>
                                            <select name="satker" id="satker" class="form-control">
                                                <option value="">-- pilih --</option>
                                                @foreach ($satker as $item)
                                                    <option value="{{$item->id}}" {{request('satker') == $item->id ? 'selected' : ''}}>{{$item->nama_satker}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="">Rekening</label>
                                            <select name="rekening" id="rekening" class="form-control">
                                               <option value="">-- pilih --</option>
                                               @foreach ($rekening as $item)
                                                   <option value="{{$item->id}}" {{request('rekening') == $item->id ? 'selected' : ''}}>{{$item->nama_rekening}}</option>
                                               @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="">Tahun</label>
                                            <select name="tahun" class="form-control" id="tahun">
                                                @foreach ($tahun as $item)
                                                    <option value="{{$item}}" {{request('tahun') == $item? 'selected' : ''}}>{{$item}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="">Bulan</label>
                                            <select name="bulan" id="bulan" class="form-control">
                                                <option value="">-- pilih --</option>
                                                @foreach ($bln as $item)
                                                    <option value="{{$item}}" {{request('bulan') == $item ? 'selected' : ''}}>{{$item}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <br>
                            <br>
                            <br>
                            <div class="row">
                                <div class="col-md-12">
                                    @if (count($data) < 1)
                                        <center>tidak ada data</center>
                                    @else
                                    <button class="btn btn-primary btn-sm" onclick="printDiv()"><i class="fa fa-print"></i> Cetak</button>
                                    <br>
                                    <br>
                                    <table class="table table-border" id="tableRekonsiliasi">
                                        <thead>
                                            <tr>
                                                <th>LPJ No : {{$lpj->no_dokumen}}</th>
                                                <th>Hasil Transaksi</th>
                                                <th>Hasil LPJ</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($kategori as $item)
                                                <tr>
                                                    <td>{{$item['name']}}</td>
                                                    <td style="text-align:right">Rp. {{number_format($data[$item['name']]['hasil_transaksi'],2,',','.')}}</td>
                                                    <td style="text-align:right">Rp. {{number_format($data[$item['name']]['hasil_lpj'],2,',','.')}}</td>
                                                    <td>{{$data[$item['name']]['status']}}</td>
                                                </tr>
                                            @endforeach                                               
                                        </tbody>
                                    </table>
                                    @endif
                                    
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
           </div>
        </section>
        <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->
@endsection
@push('js')
    <!-- page script -->
    <script>
    $(function () {
        $('#satker,#rekening,#tahun,#bulan').on('change', function(){
            $('#download').val(false);
           $('#formRekon').submit();
        });
    })

    function printDiv(){
       $('#download').val(true);
       $('#formRekon').submit();
    }
    </script>
@endpush