<?php
require_once __DIR__ . '/../modelo/pedidocompleto.php';
require_once __DIR__ . '/../modelo/producto.php';
require_once __DIR__ . '/../modelo/clientes.php';
require_once __DIR__ . '/../modelo/estadopedido.php';
require_once __DIR__ . '/../modelo/metodospago.php';
require_once __DIR__ . '/../modelo/reposteras.php';
include __DIR__ . '/partials/header.php';

function dmH($value) { return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function dmCOP($value) {
    $dec = round((float)$value, 2);
    return '$' . number_format($dec, floor($dec) == $dec ? 0 : 2, ',', '.');
}
function dmClave($texto) {
    return strtolower(strtr(trim($texto), ['ó'=>'o','Ó'=>'o','í'=>'i','Í'=>'i','á'=>'a','Á'=>'a','é'=>'e','É'=>'e','ú'=>'u','Ú'=>'u']));
}
function dmGet($name) { return is_string($_GET[$name] ?? null) ? trim($_GET[$name]) : ''; }
$modelo = new PedidoCompleto();
$pedidos = $modelo->leer_pedidos();
$productos = array_column((new Producto())->leer_productos(), null, 'id_producto');
$catalogos = [
    'id_cliente' => array_column((new Cliente())->leer_clientes(), 'nombre', 'id_cliente'),
    'id_estado_pedido' => array_column((new EstadoPedido())->leer_estados_pedido(), 'estado_pedido', 'id_estado_pedido'),
    'id_metodo_pago' => array_column((new MetodoPago())->leer_metodos_pago(), 'metodo_pago', 'id_metodos_pago'),
    'id_repostera_asignada' => array_column((new Repostera())->leer_reposteras(), 'nombre_repostera', 'id_repostera_asignada')
];
$labels = ['id_cliente'=>'Cliente','id_estado_pedido'=>'Estado','id_metodo_pago'=>'Método de pago','id_repostera_asignada'=>'Repostera'];
$nombre = static function($campo, $id) use ($catalogos, $labels) {
    return $catalogos[$campo][$id] ?? ($id ? $labels[$campo] . ' #' . $id . ' (histórico)' : 'Sin asignar');
};
$hoy = (new DateTimeImmutable('now', new DateTimeZone('America/Bogota')))->format('Y-m-d');
$vista = in_array(dmGet('vista'), ['todos','proximas'], true) ? dmGet('vista') : 'hoy';
$buscar = dmGet('buscar');
$estado = dmGet('estado');
$repostera = dmGet('repostera');
$params = compact('vista','buscar','estado','repostera');
$url = static function($cambios) use ($params) { return '?' . http_build_query(array_merge($params, $cambios)); };
$editar = dmGet('editar') !== '' ? $modelo->buscar_pedido((int)dmGet('editar')) : null;
$aviso = $_SESSION['pedidos_aviso'] ?? null;
unset($_SESSION['pedidos_aviso']);
$filas = array_filter($pedidos, static function($p) use ($vista,$buscar,$estado,$repostera,$hoy,$nombre) {
    $dia = substr((string)$p['fecha_estimada_entrega'], 0, 10);
    $texto = implode(' ', [$p['codigo_pedido'], '#'.$p['id_pedido'], $p['nombre_producto'], $nombre('id_cliente',$p['id_cliente'])]);
    return ($vista === 'todos' || ($vista === 'hoy' ? $dia === $hoy : $dia > $hoy))
        && ($estado === '' || (string)$p['id_estado_pedido'] === $estado)
        && ($repostera === '' || (string)$p['id_repostera_asignada'] === $repostera)
        && str_contains(dmClave($texto), dmClave($buscar));
});
usort($filas, static function($a,$b) use ($vista) {
    return ($vista === 'proximas' ? strcmp($a['fecha_estimada_entrega'],$b['fecha_estimada_entrega']) : 0)
        ?: ((int)$b['id_pedido'] <=> (int)$a['id_pedido']);
});
$paginas = max(1,(int)ceil(count($filas)/10));
$pagina = min($paginas,max(1,(int)dmGet('pagina')));
$estados = [];
foreach ($catalogos['id_estado_pedido'] as $id => $etiqueta) { $estados[dmClave($etiqueta)] = $id; }
$pasos = ['pendiente'=>['en preparacion','Iniciar preparación'], 'en preparacion'=>['listo para entregar','Marcar como listo'],
    'listo para entregar'=>['entregado','Confirmar entrega']];
$accionUrl = '../controlador/PedidoCompletoController.php';
function dmCampos($accion,$id,$vista) { ?>
<input type="hidden" name="csrf_token" value="<?= dmH($_SESSION['csrf_token']) ?>">
<input type="hidden" name="accion" value="<?= dmH($accion) ?>"><input type="hidden" name="vista" value="<?= dmH($vista) ?>">
<?php if ($id) { ?><input type="hidden" name="id" value="<?= (int)$id ?>"><?php } }
?>
<style>
.dm-ped{--uva:#68376f;--borde:#e7dce8;color:#332735;background:#fff7fa;border-radius:1rem;padding:clamp(.8rem,3vw,2rem);max-width:1120px;margin:auto}
.dm-ped .dm-linea{display:flex;align-items:center;justify-content:space-between;gap:.65rem;flex-wrap:wrap}
.dm-ped h1,.dm-ped h2{font-weight:700;letter-spacing:-.025em}.dm-ped h2{font-size:1.2rem}
.dm-ped .dm-btn,.dm-ped .dm-tab{display:inline-block;border:1px solid var(--borde);background:white;color:var(--uva);border-radius:.75rem;padding:.55rem .85rem;text-decoration:none;font-weight:600}
.dm-ped .dm-fuerte{background:var(--uva);color:white;border-color:var(--uva)}.dm-ped .dm-activa{background:#ede3fa}
.dm-ped .dm-tabs,.dm-ped .dm-acciones{display:flex;flex-wrap:wrap;gap:.5rem;margin:.8rem 0}
.dm-ped .dm-card,.dm-ped .dm-filtros{border:1px solid var(--borde);background:white;border-radius:1rem;padding:1rem;margin:.7rem 0}
.dm-ped .dm-card{border-inline-start:4px solid #bd97c5}.dm-ped .dm-estado{padding:.3rem .65rem;border-radius:2rem;background:#eee3f4;color:#503052;font-weight:600;font-size:.85rem}
.dm-ped .dm-entregado{background:#d9f2e1;color:#205733}.dm-ped .dm-cancelado{background:#fce3e7;color:#862637}.dm-ped .dm-pendiente{background:#fff0c9;color:#694a00}
.dm-ped dialog{width:min(640px,95vw);max-height:90dvh;overflow:auto;border:1px solid var(--borde);border-radius:1.2rem;color:inherit;padding:1.4rem}.dm-ped dialog::backdrop{background:#26172888}
.dm-ped .dm-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.75rem}.dm-ped label{font-weight:600}.dm-ped summary{cursor:pointer;color:var(--uva);font-weight:600}
@media(max-width:600px){.dm-ped .dm-grid{grid-template-columns:1fr}.dm-ped dialog{width:100%;max-width:100%;max-height:94dvh;margin:auto 0 0;border-radius:1.25rem 1.25rem 0 0}}
</style>
<section class="dm-ped" id="dm-pedidos">
<div class="dm-linea"><h1>Pedidos</h1><button class="dm-btn dm-fuerte" data-abrir="dm-form">+ Nuevo pedido</button></div>
<?php if ($aviso): ?><div role="status" class="alert alert-<?= dmH($aviso[0]) ?>"><?= dmH($aviso[1]) ?></div><?php endif; ?>
<nav class="dm-tabs" aria-label="Entregas"><?php foreach (['hoy'=>'Hoy','proximas'=>'Próximas','todos'=>'Todos'] as $k=>$v): ?>
<a class="dm-tab <?= $vista===$k?'dm-activa':'' ?>" <?= $vista===$k?'aria-current="page"':'' ?> href="<?= dmH($url(['vista'=>$k])) ?>"><?= $v ?></a><?php endforeach; ?></nav>
<form class="dm-filtros" method="get"><input type="hidden" name="vista" value="<?= dmH($vista) ?>">
<div class="input-group"><input class="form-control" name="buscar" aria-label="Buscar por cliente, producto o código" placeholder="Cliente, producto o código" value="<?= dmH($buscar) ?>"><button class="dm-btn">Buscar</button></div>
<details class="mt-2" <?= $estado!==''||$repostera!==''?'open':'' ?>><summary>Filtros</summary><div class="dm-grid my-2">
<?php foreach (['estado'=>'id_estado_pedido','repostera'=>'id_repostera_asignada'] as $key=>$campo): ?>
<label><?= $labels[$campo] ?><select class="form-select" name="<?= $key ?>"><option value="">Todos</option><?php foreach($catalogos[$campo] as $id=>$val): ?>
<option value="<?= (int)$id ?>" <?= (string)$id===$params[$key]?'selected':'' ?>><?= dmH($val) ?></option><?php endforeach; ?></select></label><?php endforeach; ?></div>
<button class="dm-btn">Aplicar filtros</button> <a href="?vista=<?= $vista ?>">Limpiar</a></details></form>
<?php if (!$filas): ?><div class="dm-card"><h2><?= $buscar || $estado || $repostera ? 'Ningún pedido coincide con los filtros' : ($vista==='hoy'?'Hoy no tienes entregas programadas':'No hay pedidos en esta vista') ?></h2><a href="?vista=todos">Ver todos los pedidos</a></div><?php endif; ?>
<?php foreach (array_slice($filas,($pagina-1)*10,10) as $p):
$id=(int)$p['id_pedido']; $codigo=$p['codigo_pedido'] ?: '#'.$id;
$status=$nombre('id_estado_pedido',$p['id_estado_pedido']); $clave=dmClave($status);
$paso=$pasos[$clave]??null; $producto=$p['nombre_producto'] ?: ($p['categoria_postre'] ?: 'Producto histórico');
?>
<article class="dm-card"><div class="dm-linea"><small><?= dmH($codigo) ?> · Entrega <?= dmH($p['fecha_estimada_entrega']===$hoy?'hoy':$p['fecha_estimada_entrega']) ?></small>
<span class="dm-estado dm-<?= dmH(str_replace(' ','-',$clave)) ?>"><?= dmH($status) ?></span></div>
<h2 class="mt-2"><?= dmH($nombre('id_cliente',$p['id_cliente'])) ?></h2>
<div class="dm-linea"><span><?= (int)$p['cantidad'] ?> × <?= dmH($producto) ?></span><strong><?= dmCOP($p['subtotal']) ?></strong></div>
<div class="dm-acciones">
<?php if ($paso && isset($estados[$paso[0]])): ?>
<form action="<?= $accionUrl ?>" method="post"><?php dmCampos('estado',$id,$vista); ?>
<input type="hidden" name="anterior" value="<?= (int)$p['id_estado_pedido'] ?>"><input type="hidden" name="nuevo" value="<?= (int)$estados[$paso[0]] ?>"><button class="dm-btn dm-fuerte"><?= dmH($paso[1]) ?></button></form>
<?php elseif (!in_array($clave,['entregado','cancelado'],true) && count($catalogos['id_estado_pedido'])>1): ?>
<form action="<?= $accionUrl ?>" method="post" class="dm-linea"><?php dmCampos('estado',$id,$vista); ?>
<input type="hidden" name="anterior" value="<?= (int)$p['id_estado_pedido'] ?>">
<label><select required name="nuevo" class="form-select" aria-label="Nuevo estado del pedido <?= dmH($codigo) ?>"><option value="">Selecciona un estado</option>
<?php foreach ($catalogos['id_estado_pedido'] as $eid=>$texto): if((int)$eid===(int)$p['id_estado_pedido']) continue; ?>
<option value="<?= (int)$eid ?>"><?= dmH($texto) ?></option><?php endforeach; ?></select></label><button class="dm-btn">Cambiar estado</button></form>
<?php endif; ?><button class="dm-btn" data-abrir="dm-det-<?= $id ?>">Ver detalle</button></div>
<dialog id="dm-det-<?= $id ?>" aria-labelledby="titulo-<?= $id ?>"><div class="dm-linea"><h2 id="titulo-<?= $id ?>">Pedido <?= dmH($codigo) ?></h2><button class="dm-btn" data-cerrar>Cerrar</button></div>
<p><?= dmH($status) ?></p><dl>
<?php foreach ($labels as $campo=>$etiqueta): if($campo==='id_estado_pedido') continue; ?><dt><?= $etiqueta ?></dt><dd><?= dmH($nombre($campo,$p[$campo])) ?></dd><?php endforeach; ?>
<dt>Producto</dt><dd><?= dmH($producto) ?> · <?= (int)$p['cantidad'] ?> × <?= dmCOP($p['precio_unitario']) ?></dd>
<dt>Subtotal</dt><dd><?= dmCOP($p['subtotal']) ?></dd>
<?php foreach(['fecha_pedido'=>'Fecha de pedido','fecha_estimada_entrega'=>'Entrega prevista','fecha_pago'=>'Fecha de pago'] as $k=>$label): ?><dt><?= $label ?></dt><dd><?= dmH($p[$k] ?: 'Sin registrar') ?></dd><?php endforeach; ?></dl>
<div class="dm-acciones"><a class="dm-btn" href="<?= dmH($url(['editar'=>$id,'pagina'=>$pagina])) ?>">Editar pedido</a>
<form class="dm-eliminar" action="<?= $accionUrl ?>" method="post" data-codigo="<?= dmH($codigo) ?>"><?php dmCampos('eliminar',$id,$vista); ?><button class="btn btn-outline-danger">Eliminar</button></form></div></dialog></article>
<?php endforeach; ?>
<?php if($paginas>1): ?><nav class="dm-linea" aria-label="Páginas"><?php if($pagina>1): ?><a href="<?= dmH($url(['pagina'=>$pagina-1])) ?>">Anterior</a><?php endif; ?> Página <?= $pagina ?> de <?= $paginas ?>
<?php if($pagina<$paginas): ?><a href="<?= dmH($url(['pagina'=>$pagina+1])) ?>">Siguiente</a><?php endif; ?></nav><?php endif; ?>
<?php
$formularios = ['dm-form'=>[]];
if ($editar) { $formularios['dm-editar']=$editar; }
foreach ($formularios as $ident=>$registro):
?>
<dialog id="<?= $ident ?>" aria-labelledby="<?= $ident ?>-titulo">
<div class="dm-linea"><h2 id="<?= $ident ?>-titulo"><?= $registro?'Editar pedido '.dmH($registro['codigo_pedido'] ?: '#'.$registro['id_pedido']):'Nuevo pedido' ?></h2><button type="button" class="dm-btn" data-cerrar>Cerrar</button></div>
<form class="dm-editor" action="<?= $accionUrl ?>" method="post" data-original="<?= dmH($registro['id_producto']??'') ?>" data-precio="<?= dmH($registro['precio_unitario']??'') ?>">
<?php dmCampos($registro?'actualizar':'crear',$registro['id_pedido']??null,$vista); ?>
<div class="dm-grid my-3">
<?php foreach(['id_cliente','fecha_pedido','fecha_estimada_entrega','id_producto','cantidad','id_repostera_asignada','id_estado_pedido','id_metodo_pago','fecha_pago'] as $campo):
if(isset($catalogos[$campo])): $actual=$registro[$campo]??''; ?>
<label><?= $labels[$campo] ?><select class="form-select" name="<?= $campo ?>" required><option value="">Seleccionar…</option>
<?php if($actual && !isset($catalogos[$campo][$actual])): ?><option selected value="<?= (int)$actual ?>"><?= dmH($nombre($campo,$actual)) ?></option><?php endif; ?>
<?php foreach($catalogos[$campo] as $id=>$valor): ?><option value="<?= (int)$id ?>" <?= (string)$id===(string)$actual?'selected':'' ?>><?= dmH($valor) ?></option><?php endforeach; ?></select></label>
<?php elseif($campo==='id_producto'): ?>
<label>Producto<select class="form-select" name="id_producto" required><option value="">Selecciona un producto</option>
<?php foreach($productos as $id=>$prod): ?><option value="<?= (int)$id ?>" data-precio="<?= dmH($prod['precio_base']) ?>" <?= (string)$id===(string)($registro['id_producto']??'')?'selected':'' ?>><?= dmH($prod['nombre_producto']) ?></option><?php endforeach; ?></select></label>
<?php elseif($campo==='cantidad'): ?><label>Cantidad<input class="form-control" name="cantidad" type="number" min="1" step="1" required value="<?= (int)($registro['cantidad']??1) ?>"></label>
<?php else: ?><label><?= ['fecha_pedido'=>'Fecha de pedido','fecha_estimada_entrega'=>'Fecha de entrega','fecha_pago'=>'Fecha de pago'][$campo] ?>
<input type="date" name="<?= $campo ?>" class="form-control" required value="<?= dmH($registro[$campo]??($campo==='fecha_pedido'?$hoy:'')) ?>"></label>
<?php endif; endforeach; ?></div>
<p class="dm-linea"><span>Precio unitario <b data-unitario></b></span><strong>Subtotal <output aria-live="polite"></output></strong></p>
<button class="dm-btn dm-fuerte"><?= $registro?'Guardar cambios':'Crear pedido' ?></button></form></dialog>
<?php endforeach; ?></section>
<script>
(() => {
    const root = document.getElementById('dm-pedidos');
    root.querySelectorAll('[data-abrir]').forEach(button => button.addEventListener('click', () => {
        document.getElementById(button.dataset.abrir).showModal();
    }));
    root.querySelectorAll('[data-cerrar]').forEach(button => button.addEventListener('click', () => button.closest('dialog').close()));
    root.querySelectorAll('.dm-eliminar').forEach(form => form.addEventListener('submit', event => {
        if (!confirm('¿Retirar el pedido ' + form.dataset.codigo + ' de la lista?')) event.preventDefault();
    }));
    const formato = new Intl.NumberFormat('es-CO', {style:'currency', currency:'COP',minimumFractionDigits:0,maximumFractionDigits:2});
    root.querySelectorAll('.dm-editor').forEach(form => {
        const campos = form.elements;
        function actualizar() {
            const producto = campos.id_producto;
            const precio = producto.value && producto.value === form.dataset.original
                ? Number(form.dataset.precio) : Number(producto.selectedOptions[0]?.dataset.precio || 0);
            campos.fecha_estimada_entrega.min = campos.fecha_pedido.value;
            form.querySelector('[data-unitario]').textContent = formato.format(precio);
            form.querySelector('output').value = formato.format(precio * Number(campos.cantidad.value));
        }
        form.addEventListener('input', actualizar);
        actualizar();
    });
    const editar = document.getElementById('dm-editar');
    if (editar) {
        editar.showModal();
        const url = new URL(location.href);
        url.searchParams.delete('editar');
        history.replaceState(null, '', url);
    }
})();
</script>
<?php include __DIR__ . '/partials/footer.php'; ?>
