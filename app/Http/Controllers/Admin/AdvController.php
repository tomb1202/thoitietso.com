<?php

namespace App\Http\Controllers\Admin;

use App\Events\MakeAdvsEvent;
use App\Http\Controllers\Controller;
use App\Models\Adv;
use App\Models\AdvType;
use Illuminate\Http\Request;

class AdvController extends Controller
{
    /**
     * Index function
     *
     * @return void
     */
    public function index()
    {
        $data =  Adv::orderBy('created_at', 'asc')->get();
        $advTypes = AdvType::where(['status' => 1])->get();

        return view('admin.adv.index', [
            'data'        => $data,
            'advTypes'    => $advTypes
        ]);
    }

    /**
     * Banner function
     *
     * @return void
     */
    public function banner()
    {
        $data =  Adv::where('type', 'LIKE', '%banner%')->orderBy('created_at', 'asc')->get();
        $advTypes = AdvType::where(['status' => 1])->whereIn('slug', ['banner'])->get();

        return view('admin.adv.banner', [
            'data'        => $data,
            'advTypes'    => $advTypes
        ]);
    }

    /**
     * Banner script function
     *
     * @return void
     */
    public function bannerScript()
    {
        $data =  Adv::where('type', 'LIKE', '%banner-script%')->orderBy('created_at', 'asc')->get();
        $advTypes = AdvType::where(['status' => 1])->whereIn('slug', ['banner-script'])->get();

        return view('admin.adv.banner-script', [
            'data'        => $data,
            'advTypes'    => $advTypes
        ]);
    }

    /**
     * Catfish function
     *
     * @return void
     */
    public function catfish()
    {
        $data =  Adv::where('type', 'LIKE', '%catfish%')->orderBy('created_at', 'asc')->get();
        $advTypes = AdvType::where(['status' => 1])->whereIn('slug', ['catfish'])->get();

        return view('admin.adv.catfish', [
            'data'        => $data,
            'advTypes'    => $advTypes
        ]);
    }

    /**
     * Preload function
     *
     * @return void
     */
    public function preload()
    {
        $data =  Adv::where('type', 'LIKE', '%preload%')->orderBy('created_at', 'asc')->get();
        $advTypes = AdvType::where(['status' => 1])->whereIn('slug', ['preload'])->get();

        return view('admin.adv.preload', [
            'data'        => $data,
            'advTypes'    => $advTypes
        ]);
    }

    /**
     * Push js function
     *
     * @return void
     */
    public function pushJs()
    {
        $data =  Adv::where('type', 'LIKE', '%push-js%')->orderBy('created_at', 'asc')->get();
        $advTypes = AdvType::where(['status' => 1])->whereIn('slug', ['push-js'])->get();

        return view('admin.adv.pushjs', [
            'data'        => $data,
            'advTypes'    => $advTypes
        ]);
    }


    /**
     * Popup js function
     *
     * @return void
     */
    public function popupJs()
    {
        $data =  Adv::where('type', 'LIKE', '%popup-js%')->orderBy('created_at', 'asc')->get();
        $advTypes = AdvType::where(['status' => 1])->whereIn('slug', ['popup-js'])->get();

        return view('admin.adv.popupjs', [
            'data'        => $data,
            'advTypes'    => $advTypes
        ]);
    }

    /**
     * textLink js function
     *
     * @return void
     */
    public function textLink()
    {
        $data =  Adv::where('type', 'LIKE', '%textlink%')->orderBy('created_at', 'asc')->get();
        $advTypes = AdvType::where(['status' => 1])->whereIn('slug', ['textlink'])->get();

        return view('admin.adv.textlink', [
            'data'        => $data,
            'advTypes'    => $advTypes
        ]);
    }

    /**
     * header js function
     *
     * @return void
     */
    public function header()
    {
        $data =  Adv::where('type', 'LIKE', '%header%')->orderBy('created_at', 'asc')->get();
        $advTypes = AdvType::where(['status' => 1])->whereIn('slug', ['header'])->get();

        return view('admin.adv.header', [
            'data'        => $data,
            'advTypes'    => $advTypes
        ]);
    }


     /**
     * bottom js function
     *
     * @return void
     */
    public function bottom()
    {
        $data =  Adv::where('type', 'LIKE', '%bottom%')->orderBy('created_at', 'asc')->get();
        $advTypes = AdvType::where(['status' => 1])->whereIn('slug', ['bottom'])->get();

        return view('admin.adv.bottom', [
            'data'        => $data,
            'advTypes'    => $advTypes
        ]);
    }

    public function refresh(){
        event(new MakeAdvsEvent());
        return true;
    }

    public function active(Request $request)
    {
        $adv = Adv::where(['id' => $request->id])->first();

        if (!isset($adv)) {
            return response()->json(['error' => true, 'message' => 'Advertisement does not exist']);
        }

        $adv->status = $request->status == "true" ? 1 : 0;
        $adv->save();

        event(new MakeAdvsEvent());

        return response()->json(['error' => false, 'message' => 'Updated successfully']);
    }

    public function delete(Request $request)
    {
        $adv = Adv::where(['id' => $request->id])->first();

        if (!isset($adv)) {
            return response()->json(['error' => true, 'message' => 'Advertisement does not exist']);
        }

        $adv->delete();

        event(new MakeAdvsEvent());

        return response()->json(['error' => false, 'message' => 'Deleted successfully']);
    }

    /**
     * store function
     *
     * @param Request $request
     * @return void
     */
    public function store(Request $request)
    {
        $item = Adv::findOrNew($request->id);

        $currentDesMedia = $item->des_media;
        $currentMobMedia = $item->mob_media;

        $item->title    = $request->title;

        if ($request->type != '') {
            $item->type = implode(', ', $request->type);
        }

        if ($request->position != '') {
            $item->position = implode(', ', $request->position);
        }

        $item->link         = $request->link ?? null;
        $item->other_link   = $request->other_link ?? null;
        $item->script       = $request->script ?? null;
        $item->supplier     = $request->supplier ?? null;
        $item->status       = $request->status ?? 1;
        $item->sort         = $request->sort ?? Adv::max('sort') + 1;

        if ($request->hasFile('des_media')) {
            $desMediaFile       = $request->file('des_media');
            $desMediaPath       = uploadFileAdv($desMediaFile, makeSlug($request->supplier) . '-des', 'uploads/advs');
            $item->des_media    = $desMediaPath;
        } else {
            $item->des_media = $currentDesMedia;
        }

        if ($request->hasFile('mob_media')) {
            $mobMediaFile       = $request->file('mob_media');
            $mobMediaPath       = uploadFileAdv($mobMediaFile, makeSlug($request->supplier) . '-mob', 'uploads/advs');
            $item->mob_media    = $mobMediaPath;
        } else {
            $item->mob_media = $currentMobMedia;
        }

        $item->save();

        event(new MakeAdvsEvent());

        return redirect()->back();
    }
}
