<x-app-layout>
<x-slot name="header">
<div class="flex justify-between items-center">
<h2 class="font-semibold text-xl text-gray-800 leading-tight">
{{ __('Gestão de Utilizadores') }}
</h2>
<a href="{{ route('admin.users.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
+ Novo Utilizador
</a>
</div>
</x-slot>

<div class="py-12">
<div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

@if (session('status'))
<div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
{{ session('status') }}
</div>
@endif

@if (session('error'))
<div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
{{ session('error') }}
</div>
@endif

<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
<div class="p-6 text-gray-900">
<table class="min-w-full text-left text-sm font-light">
<thead class="border-b font-medium dark:border-neutral-500">
<tr>
<th scope="col" class="px-6 py-4">ID</th>
<th scope="col" class="px-6 py-4">Nome</th>
<th scope="col" class="px-6 py-4">Email</th>
<th scope="col" class="px-6 py-4">Data de Registo</th>
<th scope="col" class="px-6 py-4">Ações</th>
</tr>
</thead>
<tbody>
@foreach($users as $user)
<tr class="border-b dark:border-neutral-500">
<td class="whitespace-nowrap px-6 py-4 font-medium">{{ $user->id }}</td>
<td class="whitespace-nowrap px-6 py-4">{{ $user->name }}</td>
<td class="whitespace-nowrap px-6 py-4">{{ $user->email }}</td>
<td class="whitespace-nowrap px-6 py-4">{{ $user->created_at->format('d/m/Y') }}</td>
<td class="whitespace-nowrap px-6 py-4 flex gap-2">
<a href="{{ route('admin.users.edit', $user) }}" class="text-blue-600 hover:text-blue-900 underline">Editar</a>

<form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('Tens a certeza que queres apagar este utilizador?');">
@csrf
@method('DELETE')
<button type="submit" class="text-red-600 hover:text-red-900 underline">Apagar</button>
</form>
</td>
</tr>
@endforeach
</tbody>
</table>
</div>
</div>
</div>
</div>
</x-app-layout>
