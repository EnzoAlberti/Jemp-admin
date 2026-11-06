<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all();
        return view('users.index', compact('users'));
    }

    public function create()
    {
        return view('users.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('admin.users.index')->with('status', 'Utilizador criado com sucesso!');
    }

    // Adiciona estes métodos dentro da classe UserController, após o método store()

public function edit(User $user)
{
    return view('users.edit', compact('user'));
}

public function update(Request $request, User $user)
{
    $request->validate([
        'name' => ['required', 'string', 'max:255'],
        // O email deve ser único, exceto para o próprio utilizador que estamos a editar
        'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$user->id],
        // A password é opcional na edição. Só atualiza se for preenchida.
        'password' => ['nullable', 'string', 'min:8'],
    ]);

    $user->name = $request->name;
    $user->email = $request->email;

    if ($request->filled('password')) {
        $user->password = Hash::make($request->password);
    }

    $user->save();

    return redirect()->route('admin.users.index')->with('status', 'Utilizador atualizado com sucesso!');
}

public function destroy(User $user)
{
    // Previne que o administrador se apague a si próprio por acidente
    if (auth()->id() === $user->id) {
        return redirect()->route('admin.users.index')->with('error', 'Não podes apagar a tua própria conta!');
    }

    $user->delete();

    return redirect()->route('admin.users.index')->with('status', 'Utilizador removido com sucesso!');
}
}
