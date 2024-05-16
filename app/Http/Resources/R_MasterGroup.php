<?php

namespace App\Http\Resources;

use App\Models\M_HrPosition;
use App\Models\M_MasterMenu;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class R_MasterGroup extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $position = M_HrPosition::getPositionAccessGroupList($this->id);
        $menu = M_MasterMenu::getGroupAccessMenuList($this->id);

        $result = [
            'id' => $this->id,
            'group_name' => $this->group_name,
            'status' => $this->status,
            'position_list' =>[
            ],
            'menu_list' =>[
            ]
        ];

        if (!empty($position)) {
            foreach ($position as $pos) {
                $result['position_list'][] = $pos;
            }
        }

        if (!empty($position)) {
            foreach ($menu as $pos) {
                $result['menu_list'][] = $pos;
            }
        }

        return $result;
    }
}
