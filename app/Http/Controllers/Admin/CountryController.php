<?php

namespace App\Http\Controllers\Admin;

use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;

class CountryController extends Controller
{
    public function index(Request $request)
    {
        $query = Country::query();

        if ($request->filled('search')) {
            $search = strtolower($request->search);
            $query->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"]);
        }

        $data = $query->withCount('movies')->orderBy('name')->orderBy('id')->paginate(30);

        return view('admin.countries.index', [
            'data' => $data,
            'request' => $request,
        ]);
    }

    public function create()
    {
        return view('admin.countries.form', [
            'country' => null
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255|unique:countries,name',
            'slug'        => 'nullable|string|max:255|unique:countries,slug',
            'hidden'      => 'nullable|in:0,1',
            'meta_title'  => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
        ]);

        $country = new Country();
        $country->name = $request->name;
        $country->slug = $request->slug ?: Str::slug($request->name);
        $country->hidden = $request->hidden ?? 0;
        $country->meta_title = $request->meta_title;
        $country->meta_description = $request->meta_description;
        $country->save();

        return redirect()->route('admin.countries.index')->with('success', 'Thêm quốc gia thành công!');
    }

    public function edit($id)
    {
        $country = Country::findOrFail($id);

        return view('admin.countries.form', [
            'country' => $country
        ]);
    }

    public function update(Request $request, $id)
    {
        $country = Country::findOrFail($id);

        $request->validate([
            'name'        => 'required|string|max:255|unique:countries,name,' . $country->id,
            'slug'        => 'nullable|string|max:255|unique:countries,slug,' . $country->id,
            'meta_title'  => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
        ]);

        $country->name = $request->name;
        $country->slug = $request->slug ?: Str::slug($request->name);
        $country->meta_title = $request->meta_title;
        $country->meta_description = $request->meta_description;
        $country->save();

        return redirect()->route('admin.countries.index')->with('success', 'Cập nhật quốc gia thành công!');
    }
}
