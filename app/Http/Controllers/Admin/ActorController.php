<?php

namespace App\Http\Controllers\Admin;

use App\Models\Actor;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;

class ActorController extends Controller
{
    public function index(Request $request)
    {
        $query = Actor::query();

        if ($request->filled('search')) {
            $search = strtolower($request->search);
            $query->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"]);
        }

        $data = $query->withCount('movies')->orderBy('id')->paginate(30);

        return view('admin.actors.index', [
            'data' => $data,
            'request' => $request,
        ]);
    }

    public function create()
    {
        return view('admin.actors.form', ['actor' => null]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255|unique:actors,name',
            'slug'        => 'nullable|string|max:255|unique:actors,slug',
        ]);

        $actor = new Actor();
        $actor->name = $request->name;
        $actor->slug = $request->slug ?: Str::slug($request->name);
        $actor->save();

        return redirect()->route('admin.actors.index')->with('success', 'Thêm diễn viên thành công!');
    }

    public function edit($id)
    {
        $actor = Actor::findOrFail($id);

        return view('admin.actors.form', ['actor' => $actor]);
    }

    public function update(Request $request, $id)
    {
        $actor = Actor::findOrFail($id);

        $request->validate([
            'name'        => 'required|string|max:255|unique:actors,name,' . $actor->id,
            'slug'        => 'nullable|string|max:255|unique:actors,slug,' . $actor->id,
        ]);

        $actor->name = $request->name;
        $actor->slug = $request->slug ?: Str::slug($request->name);
        $actor->save();

        return redirect()->route('admin.actors.index')->with('success', 'Cập nhật diễn viên thành công!');
    }
}
