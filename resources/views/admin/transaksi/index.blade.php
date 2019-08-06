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
                                <form action="{{route('admin.transaksi.index')}}" method="GET" id="form">
                                    <input type="hidden" name="download" value="false" id="download">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="">Satuan Kerja</label>
                                            <select name="satker" id="satker" class="form-control">
                                                @if(auth()->user()->role == "Admin")
                                                <option value="all">Semua</option>
                                                @endif
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
                                               <option value="all">Semua</option>
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
                                                <option value="all">Semua</option>
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
                                                <option value="all">Semua</option>
                                                @foreach ($bln as $key => $item)
                                                    <option value="{{$key}}" {{request('bulan') == $key ? 'selected' : ''}}>{{$item}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="">Kategori</label>
                                            <select name="kategori" id="kategori" class="form-control">
                                                <option value="all">Semua</option>
                                                @foreach ($kategori as $item)
                                                    <option value="{{$item['value']}}" {{request('status') == $item['value'] ? 'selected' : ''}}>{{$item['value']}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="">Metode Pembayaran</label>
                                                <select name="metode" id="metode" class="form-control">
                                                    <option value="all">Semua</option>
                                                    <option value="Transfer" {{request('metode') == 'Transfer' ? 'selected' : '' }}>Transfer</option>
                                                    <option value="Cek" {{request('metode') == 'Cek' ? 'selected' : ''}}>Cek</option>
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
                                    <table class="table" id="datatables">
                                        <thead>
                                            <tr>
                                                <td>No.</td>
                                                @foreach ($form as $item)
                                                    @if (array_key_exists('view_index',$item) && $item['view_index'])
                                                        @if(array_key_exists('format',$item) && $item['format'] == 'rupiah')
                                                            <td>{{$item['label']}} (Rp)</td>
                                                        @else
                                                            <td>{{$item['label']}}</td>
                                                        @endif
                                                    @endif
                                                @endforeach
                                                <td>Opsi</td>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($data as $key => $row)
                                                <tr>
                                                    <td>{{$key+1}}</td>
                                                    @foreach ($form as $item)
                                                        @if (array_key_exists('view_index',$item) && $item['view_index'])
                                                            <td @if(array_key_exists('format',$item) && $item['format'] == 'rupiah') style="text-align:right" @endif>
                                                                @if (array_key_exists('view_relation',$item))
                                                                {{ AppHelper::viewRelation($row,$item['view_relation']) }}
                                                                @else
                                                                    @if(array_key_exists('format',$item) && $item['format'] == 'rupiah')
                                                                     {{number_format($row->{$item['name']},2,',','.')}}
                                                                    @else
                                                                    {{ $row->{$item['name']} }}
                                                                    @endif
                                                                @endif
                                                            </td>
                                                        @endif
                                                    @endforeach
                                                    <td>
                                                        <a href="{{route("$template->route".'.edit',[$row->id])}}" class="btn btn-success btn-sm {{AppHelper::config($config,'index.edit.is_show') ? '' : 'hidden'}}">Ubah</a>
                                                        <a href="{{route("$template->route".'.show',[$row->id])}}" class="btn btn-info btn-sm {{AppHelper::config($config,'index.show.is_show') ? '' : 'hidden'}}">Lihat</a>
                                                        <a href="#" class="btn btn-danger btn-sm {{AppHelper::config($config,'index.delete.is_show') ? '' : 'hidden'}}" onclick="confirm('Lanjutkan ?') ? $('#frmDelete{{$row->id}}').submit() : ''">Hapus</a>
                                                        <form action="{{route("$template->route".'.destroy',[$row->id])}}" method="POST" id="frmDelete{{$row->id}}">
                                                            {{ csrf_field() }}
                                                            @method('DELETE')
                                                        </form>
                                                    </td>
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
        $('#satker,#rekening,#tahun,#bulan,#kategori,#metode').on('change', function(){
           $('#form').submit();
        });
    })

    function printDiv(){
       $('#download').val(true);
       $('#form').submit();
    }
    </script>
@endpush