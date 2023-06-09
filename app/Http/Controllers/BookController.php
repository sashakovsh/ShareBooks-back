<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $books = Book::withCount('favourites')->take(18)->get()->map(function ($book) {
            $category = Category::find($book->category);
            $book['category'] = $category->category;
            return $book;
        });
    
        return response()->json($books);    
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */

    public function store(Request $request)
    {

        $validatedData = $request->validate([
            'img' => 'nullable|image|string',
            'name' => 'required|string',
            'author' => 'required|string',
            'description' => 'nullable|string',
            'category' => 'required|string'
        ]);

        if ($request->hasFile('img')) {
            $image = $validatedData['img'];


            $extension = $image->getClientOriginalExtension();
            $filename = Str::uuid() . '.' . $extension;


            $path = $image->storeAs('/books/images', $filename, 'public');


            $imagePath = Storage::url($path);
        } elseif ($request->filled('img') && is_string($request->img)) {
            $imagePath = $request->img;
        } else {
            $imagePath = null;
        }
        $name = $validatedData['name'];
        $author = $validatedData['author'];
        $description = $validatedData['description'];
        $category = $validatedData['category'];


        Book::create([
            'name' => $name,
            'author' => $author,
            'img' => $imagePath,
            'description' => $description,
            'category' => $category
        ]);
        return response()->json(['message' => 'Book stored successfully']);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {

        $book = Book::where('id', $id)->withCount('favourites')->get();

        return response()->json($book);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Book $book)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Book $book)
    {

        $validatedData = $request->validate([
            'img' => 'nullable|image|string',
            'name' => 'required|string',
            'author' => 'required|string',
            'description' => 'nullable|string',
            'category' => 'required|string'
        ]);


        $model = Book::find($request->id);

        if (!$model) {
            return response()->json(['message' => 'Image not found'], 404);
        }

        $model->name = $validatedData['name'];
        $model->description = $validatedData['description'];
        $model->category = $validatedData['category'];

        $model->save();

        return response()->json(['message' => 'Book updated successfully']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Book $book)
    {
        try {
            $book->delete();
            return response()->json('ok');
        } catch (Exception $exception) {
            Log::error($exception->getMessage(), $exception->getTrace());
            return response()->json('error', '400');
        }
    }

    public function search(Request $request)
    {
        $query = $request->input('query');

        $books = Book::where('name', 'ILIKE', '%' . $query . '%')
                        ->orWhere('author', 'ILIKE', '%' . $query . '%')
                        ->withCount('favourites')
                        ->get();
    
        return response()->json($books);
    }
    
}
