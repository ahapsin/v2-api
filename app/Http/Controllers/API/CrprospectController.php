<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\M_CrProspect;
use App\Models\M_CrProspectAttachment;
use App\Models\M_CrProspectCol;
use App\Models\M_CrProspectPerson;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Ramsey\Uuid\Uuid;

class CrprospectController extends Controller
{
    public function index(Request $req){
        
        $data =  M_CrProspect::whereNull('deleted_at')->get();

        ActivityLogger::logActivity($req,"Success",200);
        return response()->json(['message' => 'OK',"status" => 200,'response' => $this->resourceData($data)], 200);
    }

    private function resourceData($data)
    {
        $arrayList=[];
        foreach ($data as $data) {
          
            $item = [
                'id' => $data['id'],
                'ao_id' => $data['ao_id'],
                'visit_date' => $data['visit_date'],
                'tujuan_kredit' => $data['tujuan_kredit'],
                'jenis_produk' => $data['jenis_produk'],
                'plafond' => $data['plafond'],
                'tenor' => $data['tenor'],
                'nama' => $data['nama'],
                'ktp' => $data['ktp'],
                'kk' => $data['kk'],
                'tgl_lahir' => $data['tgl_lahir'],
                'alamat' => $data['alamat'],
                'hp' => $data['hp'],
                'usaha' => $data['usaha'],
                'sector' => $data['sector'],
                'coordinate' => $data['coordinate'],
                'accurate' => $data['accurate'],
                'slik' => $data['slik'],
                'prospek_jaminan' => [],
                'prospek_person' => []
            ];

            $colData = DB::table('cr_prospect_col')
                            ->where('cr_prospect_id',$data['id'] )
                            ->get();

            foreach ($colData as $list) {
                if ($list->cr_prospect_id === $data['id']) {
                    $item['prospek_jaminan'][] = [
                        'id' => $list->id,
                        'type' => $list->type,
                        'collateral_value' => $list->collateral_value,
                        'description' => $list->description
                    ];
                }
            }

            $personData = DB::table('cr_prospect_person')
                            ->where('cr_prospect_id',$data['id'] )
                            ->get();

            foreach ($personData as $list) {
                if ($list->cr_prospect_id === $data['id']) {
                    $item['prospek_person'][] = [
                        'id' => $list->id,
                        "nama_jaminan" => $list->nama,
                        "ktp_jaminan" => $list->ktp,
                        "tgl_lahir_jaminan" => $list->tgl_lahir,
                        "pekerjaan_jaminan" => $list->pekerjaan,
                        "status_jaminan" => $list->status
                    ];
                }
            }
        

            $arrayList[] = $item;
        }

        return $arrayList;
    }

    public function detail(Request $req,$id)
    {
        $check = M_CrProspect::where('id',$id)->limit(1)->get(); 

        if ($check->isEmpty()) {
            return response()->json(['message' => 'Data Not Found',"status" => 404,'response' => $id], 404);
        }

        $checkDeletedItem = M_CrProspect::where('id',$id)->whereNull('deleted_at')->get(); 

        if ($checkDeletedItem->isEmpty()) {
            return response()->json(['message' => 'Data has been deleted',"status" => 200,'response' => $id], 200);
        }

        ActivityLogger::logActivity($req,"Success",200);
        return response()->json(['message' => 'OK',"status" => 200,'response' => $this->resourceData($check)], 200);
    }

    public function _validate($request)
    {

        $validator = $request->validate([
            'id' => 'required|string',
            'visit_date' => 'required|date',
            'tujuan_kredit' => 'required|string',
            'jenis_produk' => 'required|string',
            'plafond' => 'required|numeric',
            'tenor' => 'required|numeric',
            'nama' => 'required|string',
            'ktp' => 'required|numeric',
            'kk' => 'required|numeric',
            'tgl_lahir' => 'required|date',
            'alamat' => 'required|string',
            'hp' => 'required|numeric',
            'usaha' => 'required|string',
            'sector' => 'required|string',
            // 'coordinate' => 'string',
            // 'accurate' => 'string',
            'slik' => 'required|numeric',
            'collateral_value' => 'numeric'
        ]);

        return $validator;
    }
    
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $this->_validate($request);
    
            $crProspek = $this->createCrProspek($request);
    
            if ($request->has('jaminan') && is_array($request->jaminan)) {
                $this->createCrProspekCol($request, $crProspek);
            }

            if ($request->has('penjamin') && is_array($request->penjamin)) {
                $this->createCrProspekPerson($request, $crProspek);
            }
    
            DB::commit();
            ActivityLogger::logActivity($request,"Success",200);
            return response()->json(['message' => 'Kunjungan created successfully',"status" => 200,'response' => $request->all()], 200);
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
            'visit_date' => $request->visit_date,
            'tujuan_kredit' => $request->tujuan_kredit,
            'jenis_produk' => $request->jenis_produk,
            'plafond' => $request->plafond,
            'tenor' => $request->tenor,
            'nama' => $request->nama,
            'ktp' => $request->ktp,
            'kk' => $request->kk,
            'tgl_lahir' => $request->tgl_lahir,
            'alamat' => $request->alamat,
            'hp' => $request->hp,
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
        try {
            DB::beginTransaction();
            $validator = $this->_validate($req);

            $prospek = M_CrProspect::find($id);

            if (!$prospek) {
                return response()->json(['message' => 'Record not found', 'status' => 404], 404);
            }

            $prospek->update($validator);

            DB::commit();
            ActivityLogger::logActivity($req,"Success",200);
            return response()->json(['message' => 'Kunjungan updated successfully', 'status' => 200, 'response' => $prospek], 200);
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

            $check = M_CrProspect::where('id',$id)->first();

            if (!$check) {
                return response()->json(['message' => 'Kunjungan not found',"status" => 404], 404);
            }
            
            $check->update(['deleted_at' => now()]);

            DB::commit();
            ActivityLogger::logActivity($req,"Success",200);
            return response()->json(['message' => 'Kunjungan deleted successfully',"status" => 200,'response' => $id], 200);
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
                'image' => 'required|image|mimes:jpg,png,jpeg,gif,svg',
                'cr_prospect_id' =>'required|string'
            ]);

            $check = M_CrProspect::where('id',$req->cr_prospect_id)->first();
            
            if (empty($check)) {
                return response()->json(['message' => 'Cr Prospect Id Not Found',"status" => 404,'response' =>''], 404);
            }

            $image_path = $req->file('image')->store('Cr_Prospect');

            $data_array_attachment = [
                'id' => Uuid::uuid4()->toString(),
                'cr_prospect_id' => $req->cr_prospect_id,
                'attachment_path' => $image_path ?? ''
            ];

            M_CrProspectAttachment::create($data_array_attachment);

            DB::commit();
            ActivityLogger::logActivity($req,"Success",200);
            return response()->json(['message' => 'Image upload successfully',"status" => 200,'response' =>'http://192.168.1.9:9000/storage/'. $image_path], 200);
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
