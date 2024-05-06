<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\R_CrApplication;
use App\Models\CrApplication\M_CrApplication;
use App\Models\CrApplication\M_CrBusiness;
use App\Models\CrApplication\M_CrColGold;
use App\Models\CrApplication\M_CrColProperty;
use App\Models\CrApplication\M_CrColSecurities;
use App\Models\CrApplication\M_CrColVehicle;
use App\Models\CrApplication\M_CrGuarantor;
use App\Models\CrApplication\M_CrInfo;
use App\Models\CrApplication\M_CrPersonal;
use App\Models\CrApplication\M_CrReferral;
use App\Models\CrApplication\M_CrSpouse;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

class CrAppilcationController extends Controller
{
    public function index(Request $request)
    {
        try {
            $data = self::showData();

            return response()->json(['message' => 'OK',"status" => 200,'response' => $data], 200);
        } catch (\Exception $e) {
            ActivityLogger::logActivity($request,$e->getMessage(),500);
            return response()->json(['message' => $e->getMessage(),"status" => 500], 500);
        }
    }

    private function showData(){
        $datas = [
            'permohonan_pembiayaan' => "",
            'data_pribadi_pemohon' => "",
            'data_pekerjaan_usaha' => "",
            'data_suami_istri' => "",
            'data_penjaminan' => "",
            'data_jaminan' => "",
            'informasi' => "",
            'data_referensi_keluarga' => ""
        ];

        return $datas;
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {

            $request->validate([
                'application.pengajuan' => 'required|string',
                'application.jumlah_yang_diajukan' => 'required|numeric',
                'application.jangka_waktu' => 'required|string',
                'application.jenis_kredit' => 'required|string',
                'application.tujuan_penggunaan' => 'required|string',
                'application.cara_pembayaran' => 'required|string',
                'application.jenis_angsuran' => 'required|string',
                "personal.status_permohonan"=> 'required|string',
                "personal.hubungan_dengan_bpr"=> 'required|string',
                "personal.nama"=> 'required|string',
                "personal.jenis_kelamin"=> 'required|string',
                "personal.tempat_lahir"=>'required|string',
                "personal.tanggal_lahir" => 'required|date',
                "personal.pendidikan_terakhir"=> 'required|string',
                "personal.noktp_sim"=> 'required|string',
                "personal.tgl_terbit"=> 'required|date',
                "personal.status_perkawinan"=> 'required|string',
                "personal.jumlah_tanggungan"=> 'required|numeric',
                "personal.agama"=> 'required|string',
                "personal.alamat_tinggal"=> 'required|string',
                "personal.kota"=> 'required|string',
                "personal.kode_pos"=> 'required|string',
                "personal.lama_tinggal"=> 'required|string',
                "personal.telp_rumah"=> 'required_without:personal.hp',
                "personal.hp"=> 'required_without:personal.telp_rumah',
                "personal.status_tempat"=> 'required|string',
                "personal.nama_gadis_ibu"=> 'required|string',
                "personal.no_npwp"=> 'required_if:personal.npwp_flag,ada',
                "business.pekerjaan" => 'required|string',
                "business.nama_perusahan" => 'required|string',
                "business.bidang_usaha" => 'required|string',
                "business.jabatan" => 'required|string',
                "business.alamat_perusahaan" => 'required|string',
                "business.penghasilan_bersih_usaha" => 'required|numeric',
                "business.usaha_sampingan" => 'required|string',
                "business.lama_bekerja" => 'required|numeric',
                "business.pengahailan_bersih_sampingan" => 'required|numeric',
                'jaminan_kendaraan.*.nilai_jaminan' => 'numeric',
                'jaminan_bangunan.*.luas_tanah' => 'numeric',
                'jaminan_bangunan.*.luas_bangunan' => 'numeric',
                'jaminan_bangunan.*.nilai_jaminan' => 'numeric',
                'jaminan_bilyet.*.tanggal_valuta' => 'date',
                'jaminan_bilyet.*.jangka_waktu' => 'numeric',
                'jaminan_bilyet.*.nominal' => 'numeric',
                'jaminan_emas.*.nominal' => 'numeric',

                // "spouse.nama" => "",
                // "spouse.jenis_kelamin" => "",
                // "spouse.tempat_lahir" => "Jonggol",
                // "spouse.tanggal_lahir" => "1945-08-17",
                // "spouse.pendidikan_terakhir" => "",
                // "spouse.no_ktp_sim" => "",
                // "spouse.tgl_terbit" => "",
                // "spouse.berlaku" => "",
                // "spouse.pekerjaan" => "",
                // "spouse.nama_perusahaan" => "",
                // "spouse.bidang_perusahaan" => "",
                // "spouse.lama_perusahaan" => "",
                // "spouse.jabatan" => "",
                // "spouse.telp"=> 'required_without:spouse.hp',
                // "spouse.hp"=> 'required_without:spouse.telp',
                // "spouse.penghasilan_bersih_usaha" => ""
            ]);

            $set_uuid = Uuid::uuid4()->toString();

            self::insert_cr_application($request,$set_uuid);
            self::insert_cr_personal($request,$set_uuid);
            self::insert_cr_business($request,$set_uuid);
            self::insert_cr_spouse($request,$set_uuid);
            self::insert_cr_guarantor($request,$set_uuid);
            self::insert_cr_info($request,$set_uuid);
            self::insert_cr_referral($request,$set_uuid);

            if (collect($request->jaminan_kendaraan)->isNotEmpty()) {
                self::insert_cr_vehicle($request,$set_uuid);
            }

            if (collect($request->jaminan_bangunan)->isNotEmpty()) {
                self::insert_cr_property($request,$set_uuid);
            }
           
            if (collect($request->jaminan_emas)->isNotEmpty()) {
                self::insert_cr_gold($request,$set_uuid);
            }
          
            if (collect($request->jaminan_bilyet)->isNotEmpty()) {
                self::insert_cr_securities($request,$set_uuid);
            }
    
            DB::commit();
            // ActivityLogger::logActivity($request,"Success",200); 
            return response()->json(['message' => 'Application created successfully',"status" => 200], 200);
        }catch (QueryException $e) {
            DB::rollback();
            ActivityLogger::logActivity($request,$e->getMessage(),409);
            return response()->json(['message' => $e->getMessage(),"status" => 409], 409);
        } catch (\Exception $e) {
            DB::rollback();
            ActivityLogger::logActivity($request,$e->getMessage(),500);
            return response()->json(['message' => $e->getMessage(),"status" => 500], 500);
        }
    }

    private function insert_cr_application($request,$set_uuid){

        $data_cr_application =[
            'ID' => $set_uuid,
            'CR_PROSPECT_ID' => "",
            'CLEAR_FLAG'  => "",
            'APPLICATION_NUMBER'  => "",
            'CUST_CODE' => "",
            'ACCOUNT_NUMBER'  => "",
            'SUBMISSION_FLAG'  => $request->application['pengajuan'],
            'SUBMISSION_VALUE'  => floatval($request->application['jumlah_yang_diajukan']),
            'PERIOD' => floatval($request->application['jangka_waktu']),
            'CREDIT_TYPE' => $request->application['jenis_kredit'],
            'INTENDED_FOR' => $request->application['tujuan_penggunaan'],
            'TERM_OF_PAYMENT' => $request->application['cara_pembayaran'],
            'INSTALLMENT_TYPE' => $request->application['jenis_angsuran'],
            'VERSION' => 1,
            'CREATE_DATE' => Carbon::now()->format('Y-m-d'),
            'CREATE_USER' => $request->user()->id,
        ];

        M_CrApplication::create($data_cr_application);
    }

    private function insert_cr_personal($request,$set_uuid){

        $data_cr_personal =[  
            'ID' => Uuid::uuid4()->toString(),
            'CR_APPLICATION_ID' => $set_uuid,
            'PERSONAL_STATUS' => $request->personal['status_permohonan'],
            'BPR_RELATED_FLAG' => $request->personal['hubungan_dengan_bpr'],
            'NAME' => $request->personal['nama'],
            'GENDER' => $request->personal['jenis_kelamin'],
            'BIRTHPLACE' => $request->personal['tempat_lahir'],
            'BIRTHDATE' => $request->personal['tanggal_lahir'],
            'EDUCATION' => $request->personal['pendidikan_terakhir'],
            'ID_NUMBER' => $request->personal['noktp_sim'],
            'ID_ISSUE_DATE' => $request->personal['tgl_terbit'],
            'ID_VALID_DATE' => $request->personal['berlaku'] ? $request->personal['berlaku'] : null,
            'RELATIONSHIP' => $request->personal['status_perkawinan'],
            'AMENABILITY' => $request->personal['jumlah_tanggungan'],
            'RELIGION' => $request->personal['agama'],
            'ADDRESS' => $request->personal['alamat_tinggal'],
            'CITY' => $request->personal['kota'],
            'POSTAL_CODE' => $request->personal['kode_pos'],
            'STAY_PERIOD' => $request->personal['lama_tinggal'],
            'PHONE' => $request->personal['telp_rumah'],
            'PERSONAL_NUMBER' => $request->personal['hp'],
            'PROPERTY_STATUS' => $request->personal['status_tempat'],
            'MOTHER' => $request->personal['nama_gadis_ibu'],
            'TIN_NUMBER' => $request->personal['no_npwp'],
            'VERSION' => 1,
            'CREATE_DATE' => Carbon::now()->format('Y-m-d'),
            'CREATE_USER' => $request->user()->id,
        ];

         M_CrPersonal::create($data_cr_personal);
    }

    private function insert_cr_business($request,$set_uuid){

        $data_cr_business =[
            'ID' => Uuid::uuid4()->toString(),
            'CR_APPLICATION_ID' => $set_uuid,
            'BUSINESS_STATUS' => $request->business['pekerjaan'],
            'COMPANY_NAME' => $request->business['nama_perusahan'],
            'COMPANY_SECTION' => $request->business['bidang_usaha'],
            'POSITION' => $request->business['jabatan'],
            'ADDRESS' => $request->business['alamat_perusahaan'],
            'OFFICE_NUMBER_1' => $request->business['telp_perusahaan'],
            'OFFICE_NUMBER_2' => $request->business['hp'],
            'MONTHLY_NET_INCOME' => $request->business['penghasilan_bersih_usaha'],
            'SIDE_JOB' => $request->business['usaha_sampingan'],
            'BUSINESS_PERIOD' => $request->business['lama_bekerja'],
            'MONTHLY_SIDE_INCOME' => $request->business['pengahailan_bersih_sampingan'],
            'VERSION' => 1,
            'CREATE_DATE' => Carbon::now()->format('Y-m-d'),
            'CREATE_USER' => $request->user()->id
        ];

        M_CrBusiness::create($data_cr_business);
    }

    private function insert_cr_spouse($request,$set_uuid){

        $data_cr_spouse =[
            'ID' => Uuid::uuid4()->toString(),
            'CR_APPLICATION_ID' => $set_uuid,
            'NAME' => $request->spouse['nama'],
            'GENDER' => $request->spouse['jenis_kelamin'],
            'BIRTHPLACE' => $request->personal['tempat_lahir'],
            'BIRTHDATE' => $request->personal['tanggal_lahir'],
            'EDUCATION' => $request->spouse['pendidikan_terakhir'],
            'ID_NUMBER' => $request->spouse['no_ktp_sim'],
            'ID_ISSUE_DATE' => $request->spouse['tgl_terbit'],
            'ID_VALID_DATE' => $request->spouse['berlaku'],
            'OCCUPATION' => $request->spouse['pekerjaan'],
            'COMPANY_NAME' => $request->spouse['nama_perusahaan'],
            'COMPANY_SECTION' => $request->spouse['bidang_perusahaan'],
            'BUSINESS_PERIOD' => $request->spouse['lama_perusahaan'],
            'POSITION' => $request->spouse['jabatan'],
            'OFFICE_NUMBER_1' => $request->spouse['telp'],
            'OFFICE_NUMBER_2' => $request->spouse['hp'],
            'MONTHLY_NET_INCOME' => $request->spouse['penghasilan_bersih_usaha'],
            'VERSION' => 1,
            'CREATE_DATE' => Carbon::now()->format('Y-m-d'),
            'CREATE_USER' => $request->user()->id,
        ];

        M_CrSpouse::create($data_cr_spouse);
    }

    private function insert_cr_guarantor($request,$set_uuid){

        $data_cr_guarantor =[
            'ID' => Uuid::uuid4()->toString(),
            'CR_APPLICATION_ID' => $set_uuid,
            'HEADER_ID' => "",
            'NAME' => $request->guarantor['nama'],
            'BIRTHPLACE' => $request->guarantor['tempat_lahir'],
            'BIRTHDATE' => $request->guarantor['tanggal_lahir'],
            'ID_NUMBER' => $request->guarantor['no_ktp_sim'],
            'ADDRESS' => $request->guarantor['alamat_tinggal'],
            'CITY' => $request->guarantor['kota'],
            'POSTAL_CODE' => $request->guarantor['kode_pos'],
            'STAY_PERIOD' => $request->guarantor['lama_tinggal'],
            'PHONE' => $request->guarantor['telp_rumah'],
            'PERSONAL_NUMBER' => $request->guarantor['hp'],
            'RELATION' => $request->guarantor['hubungan_debitur'],
            'OCCUPATION' => $request->guarantor['pekerjaan_usah'],
            'MONTHLY_NET_INCOME' => $request->guarantor['penghasilan_bulan'],
            'VERSION' => 1,
            'CREATE_DATE' => Carbon::now()->format('Y-m-d'),
            'CREATE_USER' => $request->user()->id,
        ];

        M_CrGuarantor::create($data_cr_guarantor);
    }

    private function insert_cr_info($request,$set_uuid){
        $data_cr_application =[
            'ID' => Uuid::uuid4()->toString(),
            'CR_APPLICATION_ID' => $set_uuid,
            'PROP_TAX_NAME' => $request->informasi['pbb_atas_nama'],
            'ELECTRICITY_NAME' => $request->informasi['rek_listrik_atas_nama'],
            'WATTAGE' => $request->informasi['daya_listrik'],
            'PHONE_NAME' => $request->informasi['rek_telp_atas_nama'],
            'VERSION' => 1,
            'CREATE_DATE' => Carbon::now()->format('Y-m-d'),
            'CREATE_USER' => $request->user()->id,
        ];

        M_CrInfo::create($data_cr_application);
    }

    private function insert_cr_referral($request,$set_uuid){
        $data_cr_application =[
            'ID' => Uuid::uuid4()->toString(),
            'CR_APPLICATION_ID' => $set_uuid,
            'NAME' => $request->referral['nama'],
            'ADDRESS' => $request->referral['alamat_tinggal'],
            'CITY' => $request->referral['kota'],
            'POSTAL_CODE' => $request->referral['kode_pos'],
            'STAY_PERIOD' => $request->referral['lama_tinggal'],
            'PHONE' => $request->referral['telepon_rumah'],
            'PERSONAL_NUMBER' => $request->referral['hp'],
            'RELATIONSHIP' => $request->referral['hubungan_dengan_debitur'],
            'VERSION' => 1,
            'CREATE_DATE' => Carbon::now()->format('Y-m-d'),
            'CREATE_USER' => $request->user()->id,
        ];

        M_CrReferral::create($data_cr_application);
    }

    private function insert_cr_vehicle($request,$set_uuid){
        foreach ($request->jaminan_kendaraan as $result) {

            $data_array_col = [
                'ID' => Uuid::uuid4()->toString(),
                'CR_APPLICATION_ID' => $set_uuid,
                'HEADER_ID' => "",
                'BRAND' => $result['merk'],
                'TYPE' => $result['tipe_kendaraan'],
                'PRODUCTION_YEAR' => $result['tahun'],
                'COLOR' => $result['warna'],
                'ON_BEHALF' => $result['atas_nama'],
                'POLICE_NUMBER' => $result['no_polisi'],
                'CHASIS_NUMBER' => $result['no_rangka'],
                'ENGINE_NUMBER' => $result['no_mesin'],
                'BPKB_NUMBER' => $result['no_bpkb'],
                'VALUE' => $result['nilai_jaminan'],
                'COLLATERAL_FLAG' => "",
                'VERSION' => 1,
                'CREATE_DATE' => Carbon::now()->format('Y-m-d'),
                'CREATE_USER' => $request->user()->id,
            ];

            M_CrColVehicle::create($data_array_col);
        }
    }

    private function insert_cr_property($request,$set_uuid){
        foreach ($request->jaminan_bangunan as $result) {
            $data_array_col = [
                'ID' => Uuid::uuid4()->toString(),
                'CR_APPLICATION_ID' => $set_uuid,
                'HEADER_ID' => "",
                'CERTIFICATE_FLAG' => $result['sertifikat'],
                'CERTIFICATE_NUMBER' => $result['nomor_sertifikat'],
                'STATUS' => $result['imb_flag'],
                'IMB_NUMBER' => $result['imb_no'],
                'PROPERTY_AREA' => $result['luas_tanah'],
                'BUILDING_AREA' => $result['luas_bangunan'],
                'LOCATION' => $result['lokasi'],
                'DISTRICT' => $result['desa_kelurahan'],
                'SUB_DISTRICT' => $result['kecamatan'],
                'CITY' => $result['kota'],
                'ON_BEHALF' => $result['atas_nama'],
                'VALUE' => $result['nilai_jaminan'],
                'COLLATERAL_FLAG' => "",
                'VERSION' => 1,
                'CREATE_DATE' => Carbon::now()->format('Y-m-d'),
                'CREATE_USER' => $request->user()->id,
            ];

            M_CrColProperty::create($data_array_col);
        }
    }

    private function insert_cr_gold($request,$set_uuid){
        foreach ($request->jaminan_emas as $result) {
            $data_array_col = [
                'ID' => Uuid::uuid4()->toString(),
                'CR_APPLICATION_ID' => $set_uuid,
                'HEADER_ID' => "",
                'GOLD_CODE' => $result['nomor_code_emas'],
                'WEIGHT' => $result['berat'],
                'UNIT' => $result['unit'],
                'ONBEHALF' => $result['atas_nama'],
                'VALUE' => $result['nominal'],
                'VERSION' => 1,
                'CREATE_DATE' => Carbon::now()->format('Y-m-d'),
                'CREATE_USER' => $request->user()->id,
            ];

            M_CrColGold::create($data_array_col);
        }
    }

    private function insert_cr_securities($request,$set_uuid){
        foreach ($request->jaminan_bilyet as $result) {
            $data_array_col = [
                'ID' => Uuid::uuid4()->toString(),
                'CR_APPLICATION_ID' => $set_uuid,
                'HEADER_ID' => "",
                'NUMBER' => $result['nomor_bilyet'],
                'DATE'  => $result['tanggal_valuta'],
                'PERIOD'  => $result['jangka_waktu'],
                'ON_BEHALF'  => $result['atas_nama'],
                'VALUE'  => $result['nominal'],
                'COLLATERAL_FLAG'  => "",
                'VERSION' => 1,
                'CREATE_DATE' => Carbon::now()->format('Y-m-d'),
                'CREATE_USER' => $request->user()->id,
            ];

            M_CrColSecurities::create($data_array_col);
        }
    }
}
