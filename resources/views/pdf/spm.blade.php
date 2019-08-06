<style>
        .header{
            float: left;
            width: 100%;
            box-sizing: border-box;
            border-bottom : 2px solid black;
            padding-bottom: 5px;
            margin-bottom: 10px;
        }
        .logo{
            float: left;
            width: 15%;
            box-sizing: border-box;
            padding-top: 30px;
        }
        .text{
            float: left;
            width: 70%;
            box-sizing: border-box;
            text-align: center;
        }
        .bold{
            font-weight: bold;
        }
        .sub-title{
            font-size: 10px;
        }
        .table{
            width: 100%;
            border-collapse: collapse;
        }
        td,th{
            border : 1px solid black;
            padding: 5px;
        }
        .text-right{
            text-align: right;
        }
        .text-center{
            text-align: center;
        }
        .section{
            float: left;
            width: 100%;
            
        }
        .tte-left{
            float: left;
            width: 30%;
        }
        .tte-right{
            float: right;
            width: 30%;
        }
    </style>
    <div class="header">
        <div class="logo">
            <img src="{{config('kppn.logo')}}" width="80%" alt="">
        </div>
        <div class="text">
            <div class="title bold">KEMENTRIAN KEUANGAN REPUBLIK INDONESIA</div>
            <div class="title bold">DIREKTORAT JENDERAL PERBENDAHARAAN</div>
            <div class="title bold">KANTOR PELAYANAN PERBENDAHARAAN NEGARA DENPASAR</div>
            <br>
            <div class="sub-title">Gedung Keuangan Negara I</div>
            <div class="sub-title">Jalan Dr Kusuma Atmaja Denpasar</div>
            <div class="sub-title">Telepon 0361 - 225592, Faksimile 0361 - 225591</div>
            <div class="sub-title">Email : Website : www.kppndenpasar.depkeu.go.id</div>
        </div>
    </div>
    <div class="section text-center">
        <div class="section-text">LAPORAN SPM</div>        
        {{-- <div class="section-text">{{$satkerSelected->nama_satker}}</div> --}}
    </div>
    <br>
    <div class="section">
        {{-- Pada hari ini tanggal {{date('d-m-Y')}} telah dilakukan Rekonsiliasi Data Rekening Milik Satuan Kerja Lingkup Kementerian Negara / Lembaga yang selanjutnya disebut sebagai Rekening Antara KPPN Denpasar dengan {{$satkerSelected->nama_satker}} sampai dengan bulan {{$bulan}} {{$tahun}}: --}}
    </div>
    <br>
    
    <table class="table">
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
                    </tr>
                @endforeach
            </tbody>
        </table>
    <br>
    <br>
    <div class="tte-left">
       <div>An. Direktur Pengelolaan Kas Negara</div>
       <div>Kepala KPPN DENPASAR</div>
       <br>
       <br>
       <div>TEDDY</div>
       <div>NIP. 197011291997031001</div>
    </div>
    
    <div class="tte-right">
        <div>Satuan Kerja </div>        
        {{-- <div>{{$satkerSelected->nama_satker}}</div> --}}
        <br>
        <br>
        <br>
        {{-- <div>{{$satkerSelected->nama_bendahara}}</div> --}}
    </div>