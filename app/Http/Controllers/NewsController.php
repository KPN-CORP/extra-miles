<?php

namespace App\Http\Controllers;

use App\Models\MasterBisnisunit;
use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;

class NewsController extends Controller
{
    public function index()
    {
        $parentLink = 'Dashboard';
        $link = 'News Update';

        $news = News::withCount('newsLikes', 'newsViews')->get();

        return view('pages.admin.news.index', [
            'link' => $link,
            'parentLink' => $parentLink,
            'news' => $news,
        ]);
    }

    public function create(Request $request)
    {
        $parentLink = 'News Update';
        $link = 'Create News';
        $back = 'admin.news.index';

        $invalidFeedback = 'Please fill out this field.';

        $bisnisunits = MasterBisnisunit::whereNotIn('nama_bisnis', ['KPN Plantations', 'Others', 'Katingan'])
            ->orderBy('nama_bisnis')
            ->pluck('nama_bisnis');

        return view('pages.admin.news.create', [
            'back' => $back,
            'link' => $link,
            'parentLink' => $parentLink,
            'bisnisunits' => $bisnisunits,
            'invalidFeedback' => $invalidFeedback,
        ]);
    }

    public function store(Request $request)
    {
        $validate = $request->validate([
            'publish_date' => 'required|date',
            'image' => 'nullable|image|max:2048',
        ]);

        $publishDate = date('Y-m-d', strtotime($validate['publish_date']));

        $imagePath = null;

        if ($request->hasFile('image')) {
            $folder = 'assets/images/news';
        
            // Hitung total berita untuk membuat index baru
            $index = News::count() + 1;
        
            // Ambil ekstensi file
            $extension = $request->file('image')->getClientOriginalExtension();
        
            // Buat nama file baru: misal "news_5.jpg"
            $fileName = 'news_' . $index . '.' . $extension;
        
            // Simpan file dengan nama baru
            $imagePath = $request->file('image')->storeAs($folder, $fileName, 'public');
        }

        News::create([
            'category'         => $request->category,
            'title'            => $request->title,
            'publish_date'     => $publishDate,
            'content'          => $request->content,
            'status'           => $request->action === 'draft' ? 'Draft' : 'Publish',
            'image'            => $imagePath,
            'hashtag'          => $request->hashtag,
            'link'             => $request->link,
            'businessUnit'     => $request->business_unit ? json_encode($request->business_unit) : null,
            'created_by'       => Auth::id(),
        ]);

        return redirect()->route('admin.news.index')->with('success', 'News has been created successfully.');
    }

    public function edit($id)
    {
        $id = Crypt::decryptString($id); // Decrypt the ID if it was encrypted
        $news = News::findOrFail($id);
        $news->businessUnit = json_decode($news->businessUnit, true);

        $parentLink = 'News Management';
        $link = $news->category === 'vote' ? 'Update Voting' : 'Update News';
        $back = 'admin.news.index';
        $invalidFeedback = 'Please fill out this field.';

        $bisnisunits = MasterBisnisunit::whereNotIn('nama_bisnis', ['KPN Plantations', 'Others', 'Katingan'])
            ->orderBy('nama_bisnis')
            ->pluck('nama_bisnis');

        return view('pages.admin.news.edit', [
            'back' => $back,
            'link' => $link,
            'news' => $news,
            'parentLink' => $parentLink,
            'bisnisunits' => $bisnisunits,
            'invalidFeedback' => $invalidFeedback,
        ]);
    }

    public function update(Request $request, $id)
    {
        $id = Crypt::decryptString($id);
        $news = News::findOrFail($id);

        $validate = $request->validate([
            'publish_date' => 'required|date',
            'image' => 'nullable|image|max:2048',
        ]);

        $publishDate = date('Y-m-d', strtotime($validate['publish_date']));

        $imagePath = null;

        if ($request->hasFile('image')) {
            $folder = 'assets/images/news';
        
            // Hitung total berita untuk membuat index baru
            $index = News::count() + 1;
        
            // Ambil ekstensi file
            $extension = $request->file('image')->getClientOriginalExtension();
        
            // Buat nama file baru: misal "news_5.jpg"
            $fileName = 'news_' . $index . '.' . $extension;
        
            // Simpan file dengan nama baru
            $imagePath = $request->file('image')->storeAs($folder, $fileName, 'public');
            $news->image    = $imagePath;
        }

        $news->category     = $request->category;
        $news->title        = $request->title;
        $news->publish_date  = $publishDate;
        $news->content      = $request->content;
        $news->status       = $request->action === 'draft' ? 'Draft' : 'Publish';
        $news->hashtag      = $request->hashtag;
        $news->businessUnit = $request->business_unit ? json_encode($request->business_unit) : null;
        $news->updated_by   = Auth::id();

        $news->save();

        return redirect()->route('admin.news.index')->with('success', 'News updated successfully.');
    }

    public function archive($id)
    {
        $id = Crypt::decryptString($id);
        $news = News::findOrFail($id);
        $news->delete(); // Soft delete
        
        return redirect()->back()->with('success', 'News archived successfully.');
    }
}
