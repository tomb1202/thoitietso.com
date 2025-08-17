<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SettingController extends Controller
{
    public function index(Request $request)
    {
        $settings      = Setting::all();
        $arrSettings   = array();

        foreach ($settings as $item) {
            $arrSettings[$item->key] = $item->value;
        }

        return view('admin.setting.index', [
            'arrSettings'     => $arrSettings
        ]);
    }

    public function store(Request $request)
    {
        $setting    = new Setting;
        $inputs     = $request->all();

        $arrayKeys  = array_keys($inputs);
        try {
            DB::beginTransaction();
            Setting::whereIn('key', $arrayKeys)->delete();

            if ($inputs && count($inputs) > 0) {
                foreach ($inputs as $key => $val) {
                    if ($key != '_token') {
                        $setting            = new Setting;
                        $setting->key      = $key;

                        if ($key == 'logo' || $key == 'favicon') {
                            $val = $this->uploadFileSetting($request, $key);
                        }
                        $setting->value     = $val;
                        $setting->save();
                    }

                    array_push($arrayKeys, $key);
                }
            }
            DB::commit();


            return redirect()->back()->with('success', 'Settings updated successfully!');
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back();
        }
    }

    private function uploadFileSetting($request, $key)
    {
        $path  = optional(Setting::where(['key' => $key])->first())->value;
        if (isset($request->$key) && $request->$key != null) {
            $file       = $request->$key;
            $image      = $request->file($key);
            $name       = $key;
            $path       = uploadForSetting($file, $image, $name);
        }

        return $path;
    }
}
