<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\R_CrProspect;
use App\Models\M_CreditType;
use App\Models\M_CrProspect;
use App\Models\M_CrProspectAttachment;
use App\Models\M_CrProspectCol;
use App\Models\M_CrProspectPerson;
use App\Models\M_HrEmployee;
use App\Models\M_SlikApproval;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Ramsey\Uuid\Uuid;

class CrprospectController extends Controller
{
    public function index(Request $req){
        try {
            $ao_id = $req->user()->id;
            $data =  M_CrProspect::whereNull('deleted_at')->where('ao_id', $ao_id)->get();
            $dto = R_CrProspect::collection($data);

            ActivityLogger::logActivity($req,"Success",200);
            return response()->json(['message' => 'OK',"status" => 200,'response' => $dto], 200);
        } catch (QueryException $e) {
            ActivityLogger::logActivity($req,$e->getMessage(),409);
            return response()->json(['message' => $e->getMessage(),"status" => 409], 409);
        } catch (\Exception $e) {
            ActivityLogger::logActivity($req,$e->getMessage(),500);
            return response()->json(['message' => $e->getMessage(),"status" => 500], 500);
        }
    }

    public function show(Request $req,$id)
    {
        try {
            $check = M_CrProspect::where('id',$id)->whereNull('deleted_at')->first();

            if (!$check) {
                throw new Exception("Cr Prospect Id Not Found",404);
            }

            ActivityLogger::logActivity($req,"Success",200);
            return response()->json(['message' => 'OK',"status" => 200,'response' => self::resourceDetail($req,$check)], 200);
        } catch (\Exception $e) {
            ActivityLogger::logActivity($req,$e->getMessage(),500);
            return response()->json(['message' => $e->getMessage(),"status" => 500], 500);
        }
    }

    public function detailApproval(Request $req,$id)
    {
        try {
            $prospectID = base64_decode($id);
            $check = M_CrProspect::where('id',$prospectID)->whereNull('deleted_at')->firstOrFail();

            return response()->json(['message' => 'OK',"status" => 200,'response' => self::resourceData($check)], 200);
        } catch (ModelNotFoundException $e) {
            ActivityLogger::logActivity($req,'Data Not Found',404);
            return response()->json(['message' => 'Data Not Found',"status" => 404], 404);
        } catch (\Exception $e) {
            ActivityLogger::logActivity($req,$e->getMessage(),500);
            return response()->json(['message' => $e->getMessage(),"status" => 500], 500);
        }
    }

    private function resourceData($data)
    {
        $getEmployeID =User::where('id',$data->ao_id)->first();
        $ao = M_HrEmployee::where('ID', $getEmployeID->employee_id)->first();
        $colData = DB::table('cr_prospect_col')->where('cr_prospect_id',$data->id )->get();
        $personData = DB::table('cr_prospect_person')->where('cr_prospect_id',$data->id )->get();

        $arrayList = [
            'id' => $data->id,
            'ao_id' => $data->ao_id,
            'data_ao' =>  [
                [
                    'id_ao' => $ao->ID,
                    'nama_ao' => $ao->NAMA,
                ]
            ],
            'visit_date' => date('Y-m-d',strtotime($data->visit_date)),
            'tujuan_kredit' => $data->tujuan_kredit,
            'jenis_produk' => $data->jenis_produk,
            'plafond' => 'IDR '.number_format($data->plafond,0,",","."),
            'tenor' => "$data->tenor",
            'nama' => $data->nama,
            'ktp' => $data->ktp,
            'kk' => $data->kk,
            'tgl_lahir' => $data->tgl_lahir,
            'alamat' => $data->alamat,
            'hp' => $data->hp,
            'usaha' => $data->usaha,
            'sector' => $data->sector,
            'coordinate' => $data->coordinate,
            'accurate' => $data->accurate,
            'slik' => $data->slik,
            'prospek_jaminan' => [],
            'prospek_person' => []
        ];

        foreach ($colData as $list) {
            if ($list->cr_prospect_id === $data->id) {
                $item['prospek_jaminan'][] = [
                    'id' => $list->id,
                    'type' => $list->type,
                    'collateral_value' => 'IDR '.number_format($list->collateral_value,0,",","."),
                    'description' => $list->description
                ];
            }
        }

        foreach ($personData as $list) {
            if ($list->cr_prospect_id === $data->id) {
                $item['prospek_person'][] = [
                    'id' => $list->id,
                    "nama_jaminan" => $list->nama,
                    "ktp_jaminan" => $list->ktp,
                    "tgl_lahir_jaminan" => date('d-m-Y',strtotime($list->tgl_lahir)),
                    "pekerjaan_jaminan" => $list->pekerjaan,
                    "status_jaminan" => $list->status
                ];
            }
        }
          
        
        return $arrayList;
    }

    private function resourceDetail($request,$data)
    {
        $slik_approval = M_SlikApproval::where('CR_PROSPECT_ID',$data->id)->get();
        $colData = DB::table('cr_prospect_col')->where('cr_prospect_id',$data->id )->get();
        $personData = DB::table('cr_prospect_person')->where('cr_prospect_id',$data->id )->get();
        $attachmentData = M_CrProspectAttachment::orderBy('type','asc')->where('cr_prospect_id',$data->id )->get();
        $product = M_CreditType::where('code',$data->jenis_produk)->first();

        $arrayList = [
            'id' => $data->id,
            'ao_id' => $data->ao_id,
            'data_ao' =>  [
                [
                    'id_ao' => $request->user()->id,
                    'nama_ao' => M_HrEmployee::findEmployee($request->user()->employee_id)->NAMA,
                ]
            ],
            'visit_date' => date('d-m-Y',strtotime($data->visit_date)),
            'tujuan_kredit' => $data->tujuan_kredit,
            'jenis_produk' => [
               [
                'code' => $product->code,
                'name' => $product->codename,
                'description' => $product->description,
                'terms' => $product->terms,
                'image_path' => $product->image_path != null ? URL::to('/').'/storage/'. $product->image_path : '',
                'status' => $product->status
               ]
             ],
            'plafond' => 'IDR '.number_format($data->plafond,0,",","."),
            'tenor' => "$data->tenor",
            'nama' => $data->nama,
            'ktp' => $data->ktp,
            'kk' => $data->kk,
            'tgl_lahir' => date('d-m-Y',strtotime($data->tgl_lahir)),
            'alamat' => $data->alamat,
            'rt' => $data->rt,
            'rw' => $data->rw,
            'provinsi' => $data->province,
            'kota' => $data->city,
            'kelurahan' => $data->kelurahan,
            'kecamatan' => $data->kecamatan,
            'kode_pos' => $data->zip_code,
            'hp' => $data->hp,
            'usaha' => $data->usaha,
            'sector' => $data->sector,
            'coordinate' => $data->coordinate,
            'accurate' => $data->accurate,
            'slik' => $data->slik == "1" ? 'ya':"tidak",
            'ktp_attachment' => [],
            'kk_attachment' => [],
            'buku_nikah' => [],
            'jaminan' => [],
            'penjamin' => [],
            'prospek_attachment' => [],
            'slik_approval' => $slik_approval
        ];

        foreach ($attachmentData as $list) {
            if(strtolower($list->type) == 'ktp'){
                $arrayList['ktp_attachment'] = URL::to('/').'/storage/'.$list->attachment_path;
            }
        }

        foreach ($attachmentData as $list) {
            if(strtolower($list->type) == 'kk'){
                $arrayList['kk_attachment'] = URL::to('/').'/storage/'.$list->attachment_path;;
            }
        }

        foreach ($attachmentData as $list) {
            if(strtolower($list->type) == 'buku nikah'){
                $arrayList['buku_nikah'] = URL::to('/').'/storage/'.$list->attachment_path;;
            }
        }

        foreach ($attachmentData as $list) {
            if(str_contains($list->type, 'attachment')){
                $arrayList['prospek_attachment'][] = [
                    'id' => $list->id,
                    'type' => $list->type,
                    'path' => URL::to('/').'/storage/'.$list->attachment_path,
                ];
            }
        }

        foreach ($colData as $list) {
            $arrayList['jaminan'][] = [
                'id' => $list->id,
                'type' => $list->type,
                'collateral_value' => 'IDR '.number_format($list->collateral_value,0,",","."),
                'description' => $list->description
            ];
        }

        foreach ($personData as $list) {
            $arrayList['penjamin'][] = [
                'id' => $list->id,
                "nama_penjamin" => $list->nama,
                "ktp_penjamin" => $list->ktp,
                "tgl_lahir_penjamin" => date('d-m-Y',strtotime($list->tgl_lahir)),
                "pekerjaan_penjamin" => $list->pekerjaan,
                "status_penjamin" => $list->status
            ];    
        }
        
        
        return $arrayList;
    }

    public function _validate($request)
    {
        $validator = $request->validate([
            'nama' => 'required|string',
            'hp' => 'required|numeric',
            'alamat' => 'required|string',
            'slik' => 'required|numeric'
        ]);

        return $validator;
    }
    
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'id' => 'required|string'
            ]);

            self::_validate($request);
    
            $crProspek = self::createCrProspek($request);

            if ($request->slik == "1") {
                $data_array = [
                    'ID' => Uuid::uuid4()->toString(),
                    'CR_PROSPECT_ID' => $request->id,
                    'SLIK_RESULT' => '0:untouched'
                ];
            
                M_SlikApproval::create($data_array);

                if ($request->has('jaminan') && is_array($request->jaminan)) {
                    self::createCrProspekCol($request, $crProspek);
                }
    
                if ($request->has('penjamin') && is_array($request->penjamin)) {
                    self::createCrProspekPerson($request, $crProspek);
                }
            }
    
            DB::commit();
            ActivityLogger::logActivity($request,"Success",200);
            return response()->json(['message' => 'Kunjungan created successfully',"status" => 200], 200);
        } catch (QueryException $e) {
            DB::rollback();
            ActivityLogger::logActivity($request,$e->getMessage(),409);
            return response()->json(['message' => $e->getMessage(),"status" => 409], 409);
        } catch (\Exception $e) {
            DB::rollback();
            ActivityLogger::logActivity($request,$e->getMessage(),500);
            return response()->json(['message' => $e->getMessage(),"status" => 500], 500);
        }
    }
    
    private function createCrProspek(Request $request)
    {
        $data_array = [
            'id' => $request->id,
            'ao_id' => $request->user()->id,
            'nama' => $request->nama,
            'alamat' => $request->alamat,
            'rt' => $request->rt,
            'rw' => $request->rw,
            'province' => $request->provinsi,
            'city' => $request->kota,
            'kelurahan' => $request->kelurahan,
            'kecamatan' => $request->kecamatan,
            'zip_code' => $request->kode_pos,
            'hp' => $request->hp,
            'visit_date' => $request->visit_date,
            'tujuan_kredit' => $request->tujuan_kredit,
            'jenis_produk' => $request->jenis_produk,
            'plafond' => $request->plafond,
            'tenor' => $request->tenor,
            'ktp' => $request->ktp,
            'kk' => $request->kk,
            'tgl_lahir' => $request->tgl_lahir,
            'usaha' => $request->usaha,
            'sector' => $request->sector,
            'coordinate' => $request->coordinate,
            'accurate' => $request->accurate,
            'slik' => $request->slik
        ];
    
        return M_CrProspect::create($data_array);
    } 
    
    private function createCrProspekCol(Request $request, $crProspek)
    {
        foreach ($request->jaminan as $result) {
            $data_array_col = [
                'id' => Uuid::uuid4()->toString(),
                'cr_prospect_id' => $crProspek->id,
                'type' => $result['type'] ?? '',
                'collateral_value' => $result['collateral_value'] ?? 0,
                'description' => $result['description'] ?? ''
            ];

            M_CrProspectCol::create($data_array_col);
        }

    }
    
    private function createCrProspekPerson(Request $request, $crProspek)
    {
        foreach ($request->penjamin as $result) {
            $data_array_person = [
                'id' => Uuid::uuid4()->toString(),
                'cr_prospect_id' => $crProspek->id,
                'nama' => $result['nama'] ?? '',
                'ktp' => $result['ktp'] ?? '',
                'tgl_lahir' => $result['tgl_lahir'] ?? null,
                'pekerjaan' => $result['pekerjaan'] ?? '',
                'status' => $result['status'] ?? ''
            ];
        
            M_CrProspectPerson::create($data_array_person);
        }

    }

    public function update(Request $req, $id)
    {
        DB::beginTransaction();
        
        try {
            self::_validate($req);

            $prospek_check = M_CrProspect::where('id',$id)->whereNull('deleted_at')->first();

            if (!$prospek_check) {
                throw new Exception("Cr Prospect Id Not Found",404);
            }

            $prospect_data = [
                'nama' => $req->nama,
                'alamat' => $req->alamat,
                'rt' => $req->rt,
                'rw' => $req->rw,
                'province' => $req->provinsi,
                'city' => $req->kota,
                'kelurahan' => $req->kelurahan,
                'kecamatan' => $req->kecamatan,
                'zip_code' => $req->kode_pos,
                'hp' => $req->hp,
                'visit_date' => $req->has('visit_date')?$req->visit_date:"2024-02-02",
                'tujuan_kredit' => $req->tujuan_kredit,
                'jenis_produk' => $req->jenis_produk,
                'plafond' => $req->plafond,
                'tenor' => $req->tenor,
                'ktp' => $req->ktp,
                'kk' => $req->kk,
                'tgl_lahir' => $req->tgl_lahir,
                'usaha' => $req->usaha,
                'sector' => $req->sector,
                'coordinate' => $req->coordinate,
                'accurate' => $req->accurate,
                'slik' => $req->slik,
                'updated_by' => $req->user()->id,
                'updated_at' => now()
            ];

            $prospek_check->update($prospect_data);

            if ($req->has('jaminan') && is_array($req->jaminan)) {
                foreach ($req->jaminan as $result) {

                    $jaminan_check = M_CrProspectCol::where('id',$result['id'])->first();

                    if (!$jaminan_check) {
                        throw new Exception("Jaminan Id Not Found",404);
                    }

                    $jaminan_data = [
                        'type' => $result['type'] ?? '',
                        'collateral_value' => $result['collateral_value'] ?? 0,
                        'description' => $result['description'] ?? ''
                    ];
        
                    $jaminan_check->update($jaminan_data);
                }
            }

            if ($req->has('penjamin') && is_array($req->penjamin)) {
                foreach ($req->penjamin as $result) {

                    $penjamin_check = M_CrProspectPerson::where('id',$result['id'])->first();

                    if (!$penjamin_check) {
                        throw new Exception("Penjamin Id Not Found",404);
                    }

                    $penjamin_data = [
                        'nama' => $result['nama_penjamin'] ?? '',
                        'ktp' => $result['ktp_penjamin'] ?? '',
                        'tgl_lahir' => $result['tgl_lahir_penjamin'] ?? null,
                        'pekerjaan' => $result['pekerjaan_penjamin'] ?? '',
                        'status' => $result['status_penjamin'] ?? ''
                    ];

                    $penjamin_check->update($penjamin_data);
                }
            }

            DB::commit();
            ActivityLogger::logActivity($req,"Success",200);
            return response()->json(['message' => 'Kunjungan updated successfully', 'status' => 200], 200);
        } catch (QueryException $e) {
            DB::rollback();
            ActivityLogger::logActivity($req,$e->getMessage(),409);
            return response()->json(['message' => $e->getMessage(), 'status' => 409], 409);
        } catch (\Exception $e) {
            DB::rollback();
            ActivityLogger::logActivity($req,$e->getMessage(),500);
            return response()->json(['message' => $e->getMessage(), 'status' => 500], 500);
        }
    }

    public function destroy(Request $req,$id)
    {
        try {
            DB::beginTransaction();

            $check = M_CrProspect::findOrFail($id);

            $data = [
                'deleted_by' => $req->user()->id,
                'deleted_at' => now()
            ];
            
            $check->update($data);

            DB::commit();
            ActivityLogger::logActivity($req,"Success",200);
            return response()->json(['message' => 'Kunjungan deleted successfully',"status" => 200,'response' => $id], 200);
        } catch (ModelNotFoundException $e) {
            DB::rollback();
            ActivityLogger::logActivity($req, 'Cr Prospect Id Not Found', 404);
            return response()->json(['message' => 'Cr Prospect Id Not Found', "status" => 404], 404);
        } catch (QueryException $e) {
            DB::rollback();
            ActivityLogger::logActivity($req,$e->getMessage(),409);
            return response()->json(['message' => $e->getMessage(),"status" => 409], 409);
        } catch (\Exception $e) {
            DB::rollback();
            ActivityLogger::logActivity($req,$e->getMessage(),500);
            return response()->json(['message' => $e->getMessage(),"status" => 500], 500);
        } 
    }

    public function uploadImage(Request $req)
    {
        try {
            DB::beginTransaction();

            $this->validate($req, [
                'image' => 'image|mimes:jpg,png,jpeg,gif,svg',
                'type' => 'required|string',
                'cr_prospect_id' =>'required|string'
            ]);

            $image_path = $req->file('image')->store('Cr_Prospect');

            $url= URL::to('/') . '/storage/' . $image_path;

            $data_array_attachment = [
                'id' => Uuid::uuid4()->toString(),
                'cr_prospect_id' => $req->cr_prospect_id,
                'type' => $req->type,
                'attachment_path' => $image_path ?? ''
            ];

            M_CrProspectAttachment::create($data_array_attachment);

            DB::commit();
            ActivityLogger::logActivity($req,"Success",200);
            return response()->json(['message' => 'Image upload successfully',"status" => 200,'response' => $url], 200);
        } catch (ModelNotFoundException $e) {
            DB::rollback();
            ActivityLogger::logActivity($req, 'Cr Prospect Id Not Found', 404);
            return response()->json(['message' => 'Cr Prospect Id Not Found', "status" => 404], 404);
        } catch (QueryException $e) {
            DB::rollback();
            ActivityLogger::logActivity($req,$e->getMessage(),409);
            return response()->json(['message' => $e->getMessage(),"status" => 409], 409);
        } catch (\Exception $e) {
            DB::rollback();
            ActivityLogger::logActivity($req,$e->getMessage(),500);
            return response()->json(['message' => $e->getMessage(),"status" => 500], 500);
        } 
    }
}
