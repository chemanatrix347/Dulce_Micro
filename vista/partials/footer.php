            </div> <!-- /container-fluid -->
        </div> <!-- /content -->
    </div> <!-- /content-wrapper -->
</div> <!-- /wrapper -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
<script>
document.addEventListener('click', async (e) => {
    const img = e.target.closest('.avatar-opcion');
    if (!img) return;

    try {
        const res = await fetch('/Dulce_Micro/controlador/actualizar_avatar.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                avatar: img.dataset.avatar,
                csrf_token: '<?= $_SESSION['csrf_token'] ?>'
            })
        });
        const data = await res.json();

        if (data.ok) {
            document.getElementById('avatarActualImg').src = '/Dulce_Micro/img/' + img.dataset.avatar;
            bootstrap.Modal.getInstance(document.getElementById('modalAvatar')).hide();
        } else {
            alert(data.error || 'No se pudo actualizar el avatar');
        }
    } catch (err) {
        alert('Error de conexion al actualizar el avatar');
    }
});
</script>
</body>
</html>