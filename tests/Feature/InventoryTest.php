<?php

use App\Models\Accesorio;
use App\Models\Docente;
use App\Models\Equipo;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->withoutVite();
});

test('administrators can search equipment and view the complete equipment detail', function () {
    $admin = inventoryAdminUser();
    $equipment = inventoryEquipment([
        'codigo' => 'INV-100',
        'marca' => 'Lenovo',
        'modelo' => 'ThinkPad',
        'imagen_referencial' => 'equipos/thinkpad.jpg',
    ]);
    $otherEquipment = inventoryEquipment([
        'codigo' => 'INV-200',
        'marca' => 'Dell',
        'modelo' => 'Latitude',
    ]);
    $accessory = inventoryAccessory($equipment);

    $this->actingAs($admin)
        ->get(route('equipos.index', ['buscar' => 'INV-100']))
        ->assertSeeText('INV-100')
        ->assertDontSeeText('INV-200');

    $this->actingAs($admin)
        ->get(route('equipos.show', $equipment->id))
        ->assertSeeText('ThinkPad')
        ->assertSeeText('Número de serie')
        ->assertSeeText('Accesorios relacionados')
        ->assertSeeText($accessory->nombre)
        ->assertSee('equipos/thinkpad.jpg', false);
});

test('administrators can create update search and delete equipment', function () {
    Storage::fake('public');
    $admin = inventoryAdminUser();
    $photo = UploadedFile::fake()->image('equipo.jpg');

    $this->actingAs($admin)
        ->post(route('equipos.store'), [
            'codigo' => 'INV-300',
            'tipo_equipo' => 'Laptop',
            'marca' => 'HP',
            'modelo' => 'ProBook',
            'numero_serie' => 'SER-300',
            'modo_uso' => 'individual',
            'estado' => 'Disponible',
            'mac' => '00:11:22:33:44:55',
            'imagen_referencial' => $photo,
        ])
        ->assertRedirect(route('equipos.index'))
        ->assertSessionHas('success');

    $equipment = Equipo::where('codigo', 'INV-300')->firstOrFail();
    Storage::disk('public')->assertExists($equipment->imagen_referencial);
    $oldImage = $equipment->imagen_referencial;

    $this->actingAs($admin)
        ->put(route('equipos.update', $equipment->id), [
            'codigo' => 'INV-300',
            'tipo_equipo' => 'Laptop',
            'marca' => 'HP',
            'modelo' => 'ProBook G2',
            'numero_serie' => 'SER-300',
            'modo_uso' => 'individual',
            'estado' => 'En uso',
            'mac' => '00:11:22:33:44:55',
            'imagen_referencial' => UploadedFile::fake()->image('equipo-nuevo.jpg'),
        ])
        ->assertRedirect(route('equipos.show', $equipment->id))
        ->assertSessionHas('success');

    $equipment->refresh();
    $this->assertDatabaseHas('equipos', [
        'id' => $equipment->id,
        'modelo' => 'ProBook G2',
        'estado' => 'En uso',
    ]);
    Storage::disk('public')->assertMissing($oldImage);
    Storage::disk('public')->assertExists($equipment->imagen_referencial);

    $this->actingAs($admin)
        ->get(route('equipos.index', ['buscar' => 'ProBook G2']))
        ->assertSeeText('INV-300');

    $this->actingAs($admin)
        ->delete(route('equipos.destroy', $equipment->id))
        ->assertRedirect(route('equipos.index'));

    $this->assertDatabaseMissing('equipos', ['id' => $equipment->id]);
    Storage::disk('public')->assertMissing($equipment->imagen_referencial);
});

test('equipment with related accessories cannot be deleted', function () {
    $admin = inventoryAdminUser();
    $equipment = inventoryEquipment(['codigo' => 'INV-350']);
    inventoryAccessory($equipment);

    $this->actingAs($admin)
        ->from(route('equipos.index'))
        ->delete(route('equipos.destroy', $equipment->id))
        ->assertRedirect(route('equipos.index'))
        ->assertSessionHasErrors('equipo');

    $this->assertDatabaseHas('equipos', [
        'id' => $equipment->id,
        'codigo' => 'INV-350',
    ]);
});

test('accessory creation rejects an unsupported state', function () {
    $admin = inventoryAdminUser();
    $equipment = inventoryEquipment();

    $this->actingAs($admin)
        ->from(route('accesorios.create'))
        ->post(route('accesorios.store'), [
            'equipo_id' => $equipment->id,
            'nombre' => 'Mouse USB',
            'cantidad' => 1,
            'estado' => 'Estado inválido',
        ])
        ->assertRedirect(route('accesorios.create'))
        ->assertSessionHasErrors('estado');

    $this->assertDatabaseMissing('accesorios', [
        'equipo_id' => $equipment->id,
        'nombre' => 'Mouse USB',
    ]);
});

test('administrators can search create update view and delete accessories', function () {
    Storage::fake('public');
    $admin = inventoryAdminUser();
    $equipment = inventoryEquipment(['codigo' => 'INV-400']);

    $this->actingAs($admin)
        ->post(route('accesorios.store'), [
            'equipo_id' => $equipment->id,
            'nombre' => 'Teclado USB',
            'marca' => 'Logitech',
            'modelo' => 'K120',
            'numero_serie' => 'ACC-400',
            'cantidad' => 2,
            'estado' => 'Disponible',
            'observaciones' => 'Con distribución española.',
            'imagen_referencial' => UploadedFile::fake()->image('teclado.jpg'),
        ])
        ->assertRedirect(route('accesorios.index'))
        ->assertSessionHas('success');

    $accessory = Accesorio::where('numero_serie', 'ACC-400')->firstOrFail();
    Storage::disk('public')->assertExists($accessory->imagen_referencial);
    $oldImage = $accessory->imagen_referencial;

    $this->actingAs($admin)
        ->get(route('accesorios.index', ['buscar' => 'INV-400']))
        ->assertSeeText('Teclado USB');

    $this->actingAs($admin)
        ->get(route('accesorios.show', $accessory->id))
        ->assertSeeText('Teclado USB')
        ->assertSeeText('INV-400')
        ->assertSeeText('Con distribución española.')
        ->assertSee('accesorios/', false);

    $this->actingAs($admin)
        ->put(route('accesorios.update', $accessory->id), [
            'equipo_id' => $equipment->id,
            'nombre' => 'Teclado inalámbrico',
            'marca' => 'Logitech',
            'modelo' => 'K380',
            'numero_serie' => 'ACC-400',
            'cantidad' => 1,
            'estado' => 'En uso',
            'observaciones' => 'Asignado a laboratorio.',
            'imagen_referencial' => UploadedFile::fake()->image('teclado-nuevo.jpg'),
        ])
        ->assertRedirect(route('accesorios.show', $accessory->id))
        ->assertSessionHas('success');

    $accessory->refresh();
    $this->assertDatabaseHas('accesorios', [
        'id' => $accessory->id,
        'nombre' => 'Teclado inalámbrico',
        'modelo' => 'K380',
        'cantidad' => 1,
        'estado' => 'En uso',
    ]);
    Storage::disk('public')->assertMissing($oldImage);
    Storage::disk('public')->assertExists($accessory->imagen_referencial);

    $this->actingAs($admin)
        ->delete(route('accesorios.destroy', $accessory->id))
        ->assertRedirect(route('accesorios.index'));

    $this->assertDatabaseMissing('accesorios', ['id' => $accessory->id]);
    Storage::disk('public')->assertMissing($accessory->imagen_referencial);
});

test('teachers can view inventory but cannot create inventory records', function () {
    $teacher = inventoryTeacherUser();

    $this->actingAs($teacher)
        ->get(route('equipos.index'))
        ->assertOk()
        ->assertDontSeeText('Registrar equipo');

    $this->actingAs($teacher)
        ->get(route('accesorios.index'))
        ->assertOk()
        ->assertDontSeeText('Registrar accesorio');

    $this->actingAs($teacher)
        ->get(route('equipos.create'))
        ->assertForbidden();

    $this->actingAs($teacher)
        ->get(route('accesorios.create'))
        ->assertForbidden();
});

function inventoryAdminUser(): User
{
    $teacher = Docente::create([
        'nombres' => 'Admin',
        'apellido_paterno' => 'Inventario',
        'apellido_materno' => 'Pruebas',
        'ci' => fake()->unique()->numerify('########'),
        'cargo' => 'Administrador',
        'estado' => 'activo',
    ]);

    return User::create([
        'name' => 'Administrador inventario',
        'username' => fake()->unique()->userName(),
        'password' => 'password',
        'tipo_usuario' => 'administrador',
        'docente_id' => $teacher->id,
    ]);
}

function inventoryTeacherUser(): User
{
    $teacher = Docente::create([
        'nombres' => 'Docente',
        'apellido_paterno' => 'Inventario',
        'apellido_materno' => 'Pruebas',
        'ci' => fake()->unique()->numerify('########'),
        'cargo' => 'Docente',
        'estado' => 'activo',
    ]);

    return User::create([
        'name' => 'Docente inventario',
        'username' => fake()->unique()->userName(),
        'password' => 'password',
        'tipo_usuario' => 'docente',
        'docente_id' => $teacher->id,
    ]);
}

function inventoryEquipment(array $attributes = []): Equipo
{
    return Equipo::create($attributes + [
        'codigo' => fake()->unique()->bothify('INV-###'),
        'tipo_equipo' => 'Laptop',
        'marca' => 'Dell',
        'modelo' => 'Latitude',
        'numero_serie' => fake()->unique()->bothify('SER-###'),
        'modo_uso' => 'individual',
        'estado' => 'Disponible',
    ]);
}

function inventoryAccessory(Equipo $equipment): Accesorio
{
    return Accesorio::create([
        'equipo_id' => $equipment->id,
        'nombre' => 'Cargador',
        'cantidad' => 1,
        'estado' => 'Disponible',
    ]);
}
