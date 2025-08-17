<?php

namespace App\Http\Controllers\Admin;

use App\Models\Director;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;

class DirectorController extends Controller
{
    public function index(Request $request)
    {
        $query = Director::query();

        if ($request->filled('search')) {
            $search = strtolower($request->search);
            $query->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"]);
        }

        $data = $query->withCount('movies')->orderBy('id')->paginate(30);

        return view('admin.directors.index', [
            'data' => $data,
            'request' => $request,
        ]);
    }

    public function create()
    {
        return view('admin.directors.form', ['director' => null]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:directors,name',
            'slug' => 'nullable|string|max:255|unique:directors,slug',
        ]);

        $director = new Director();
        $director->name = $request->name;
        $director->slug = $request->slug ?: Str::slug($request->name);
        $director->save();

        return redirect()->route('admin.directors.index')->with('success', 'Thêm đạo diễn thành công!');
    }

    public function edit($id)
    {
        $director = Director::findOrFail($id);

        return view('admin.directors.form', ['director' => $director]);
    }

    public function update(Request $request, $id)
    {
        $director = Director::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:directors,name,' . $director->id,
            'slug' => 'nullable|string|max:255|unique:directors,slug,' . $director->id,
        ]);

        $director->name = $request->name;
        $director->slug = $request->slug ?: Str::slug($request->name);
        $director->save();

        return redirect()->route('admin.directors.index')->with('success', 'Cập nhật đạo diễn thành công!');
    }
}
