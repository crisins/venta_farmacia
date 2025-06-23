Limpieza realizada por GitHub Copilot:

- Eliminados los siguientes archivos relacionados a Inventario y MovimientoInventario:
  - app/Models/Inventario.php
  - app/Models/MovimientoInventario.php
  - database/factories/InventarioFactory.php
  - database/factories/MovimientoInventarioFactory.php

- Recuerda eliminar también las migraciones relacionadas a inventarios y movimientos si existen.
- Si tienes referencias a estos modelos en otros archivos, elimínalas o coméntalas.

¡El sistema ahora usa solo Producto::stock para el manejo de stock!
