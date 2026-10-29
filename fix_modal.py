content = open('resources/views/usuarios/show.blade.php', encoding='utf-8').read()

# Replace the modal structure with a cleaner version
old_modal_start = '<form @submit.prevent="recoverAccount()" class="space-y-4">'
new_modal_start = '<form @submit.prevent="recoverAccount()" class="space-y-4" id="recover-form">'

content = content.replace(old_modal_start, new_modal_start)

# Replace the @push/@endpush with just the inline script
old_script_block = content[content.index('{{-- Scripts de recuperación --}}'):]
new_script = '''<script>
        async function recoverAccount() {
            const pw = document.querySelector('input[name="admin_password"]').value;
            if (!pw) {
                alert('Introduzca la contraseña del administrador.');
                return;
            }
            const token = document.querySelector('meta[name="csrf-token"]').content;
            try {
                const res = await fetch('{{ route('usuarios.recover', ['id' => $usuario->id]) }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest' },
                    body: JSON.stringify({ admin_password: pw })
                });
                const data = await res.json();
                if (res.ok && data.success) {
                    document.getElementById('newUsername').textContent = data.username;
                    document.getElementById('newPassword').textContent = data.password;
                    document.getElementById('credentialsBox').classList.remove('hidden');
                } else {
                    alert(data.message || 'Error. Inténtelo de nuevo.');
                }
            } catch (e) {
                alert('No se pudo completar la recuperación. Inténtalo de nuevo.');
            }
        }
    </script>'''

content = content[:content.index('{{-- Scripts de recuperación --}}')] + new_script + '</x-admin-layout>'

open('resources/views/usuarios/show.blade.php', 'w', encoding='utf-8').write(content)
print('done')
