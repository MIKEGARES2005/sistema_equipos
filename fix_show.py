content = open('resources/views/usuarios/show.blade.php', encoding='utf-8').read()
content = content.replace('hover:bg:ae3232', 'hover:bg-red-700')
old = """<form method="POST" action="{{ route('usuarios.recover', $usuario->id) }}"
                  class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#C0392B] px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-red-700">
                @csrf
                @method('POST')
                <button type="submit" class="flex items-center gap-2">
                    <span aria-hidden="true"></span>
                    RECUPERAR CUENTA
                </button>
            </form>"""
new = """<button type="button"
                  class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#C0392B] px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-red-700"
                  @click="showRecover = true">
                <span aria-hidden="true"></span>
                RECUPERAR CUENTA
            </button>"""
content = content.replace(old, new)
open('resources/views/usuarios/show.blade.php', 'w', encoding='utf-8').write(content)
print('done')