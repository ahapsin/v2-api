<?php

namespace App\Models\SlikApproval;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class M_SlikFasilitas extends Model
{
    use HasFactory;
    protected $table = 'slik_fasilitas';
    protected $fillable = [
        'ID',
        'SLIK_APPROVAL_ID',
        'ljk',
        'ljkKet',
        'cabang',
        'cabangKet',
        'bakiDebet',
        'tanggalDibentuk',
        'tanggalUpdate',
        'bulan',
        'tahun',
        'sifatKreditPembiayaan',
        'sifatKreditPembiayaanKet',
        'jenisKreditPembiayaan',
        'jenisKreditPembiayaanKet',
        'akadKreditPembiayaan',
        'akadKreditPembiayaanKet',
        'noRekening',
        'frekPerpjganKreditPembiayaan',
        'noAkadAwal',
        'tanggalAkadAwal',
        'noAkadAkhir',
        'tanggalAkadAkhir',
        'tanggalAwalKredit',
        'tanggalMulai',
        'tanggalJatuhTempo',
        'kategoriDebiturKode',
        'kategoriDebiturKet',
        'jenisPenggunaan',
        'jenisPenggunaanKet',
        'sektorEkonomi',
        'sektorEkonomiKet',
        'kreditProgramPemerintah',
        'kreditProgramPemerintahKet',
        'lokasiProyek',
        'lokasiProyekKet',
        'valutaKode',
        'sukuBungaImbalan',
        'jenisSukuBungaImbalan',
        'jenisSukuBungaImbalanKet',
        'kualitas',
        'kualitasKet',
        'jumlahHariTunggakan',
        'nilaiProyek',
        'plafonAwal',
        'plafon',
        'realisasiBulanBerjalan',
        'nilaiDalamMataUangAsal',
        'kodeSebabMacet',
        'sebabMacetKet',
        'tanggalMacet',
        'tunggakanPokok',
        'tunggakanBunga',
        'frekuensiTunggakan',
        'denda',
        'frekuensiRestrukturisasi',
        'tanggalRestrukturisasiAkhir',
        'kodeCaraRestrukturisasi',
        'restrukturisasiKet',
        'kondisi',
        'kondisiKet',
        'tanggalKondisi',
        'keterangan',
        'tahunBulan01Ht',
        'tahunBulan01',
        'tahunBulan01Kol',
        'tahunBulan02Ht',
        'tahunBulan02',
        'tahunBulan02Kol',
        'tahunBulan03Ht',
        'tahunBulan03',
        'tahunBulan03Kol',
        'tahunBulan04Ht',
        'tahunBulan04',
        'tahunBulan04Kol',
        'tahunBulan05Ht',
        'tahunBulan05',
        'tahunBulan05Kol',
        'tahunBulan06Ht',
        'tahunBulan06',
        'tahunBulan06Kol',
        'tahunBulan07Ht',
        'tahunBulan07',
        'tahunBulan07Kol',
        'tahunBulan08Ht',
        'tahunBulan08',
        'tahunBulan08Kol',
        'tahunBulan09Ht',
        'tahunBulan09',
        'tahunBulan09Kol',
        'tahunBulan10Ht',
        'tahunBulan10',
        'tahunBulan10Kol',
        'tahunBulan11Ht',
        'tahunBulan11',
        'tahunBulan11Kol',
        'tahunBulan12Ht',
        'tahunBulan12',
        'tahunBulan12Kol',
        'tahunBulan13Ht',
        'tahunBulan13',
        'tahunBulan13Kol',
        'tahunBulan14Ht',
        'tahunBulan14',
        'tahunBulan14Kol',
        'tahunBulan15Ht',
        'tahunBulan15',
        'tahunBulan15Kol',
        'tahunBulan16Ht',
        'tahunBulan16',
        'tahunBulan16Kol',
        'tahunBulan17Ht',
        'tahunBulan17',
        'tahunBulan17Kol',
        'tahunBulan18Ht',
        'tahunBulan18',
        'tahunBulan18Kol',
        'tahunBulan19Ht',
        'tahunBulan19',
        'tahunBulan19Kol',
        'tahunBulan20Ht',
        'tahunBulan20',
        'tahunBulan20Kol',
        'tahunBulan21Ht',
        'tahunBulan21',
        'tahunBulan21Kol',
        'tahunBulan22Ht',
        'tahunBulan22',
        'tahunBulan22Kol',
        'tahunBulan23Ht',
        'tahunBulan23',
        'tahunBulan23Kol',
        'tahunBulan24Ht',
        'tahunBulan24',
        'tahunBulan24Kol'
    ];
    
    protected $guarded = [];
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'ID';
    public $timestamps = false;
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if ($model->getKey() == null) {
                $model->setAttribute($model->getKeyName(), Str::uuid()->toString());
            }
        });
    }
}
