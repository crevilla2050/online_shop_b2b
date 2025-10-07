import os
import json
import pymysql
from pymysql import err as pymysql_err
import urllib.request
from urllib.parse import urlparse
import pathlib

# Database connection parameters - replace with real credentials
db_config = {
    'host': 'localhost',
    'user': 'root',
    'password': 't4a2x0a6',
    'database': 'db_online_shop',
    'charset': 'utf8mb4',
    'use_unicode': True,
}

def connect_db():
    try:
        conn = pymysql.connect(
            host=db_config['host'],
            user=db_config['user'],
            password=db_config['password'],
            database=db_config['database'],
            charset=db_config.get('charset', 'utf8mb4'),
            cursorclass=pymysql.cursors.DictCursor,
            autocommit=False
        )
        print("Conexión a la base de datos exitosa.")
        return conn
    except pymysql_err.OperationalError as err:
        if err.args[0] == 1045:  # ER_ACCESS_DENIED_ERROR
            print("Error de acceso: usuario o contraseña incorrectos.")
        elif err.args[0] == 1049:  # ER_BAD_DB_ERROR
            print("La base de datos no existe.")
        else:
            print(f"Error en la conexión a la base de datos: {err}")
        raise

def get_category_id(cursor, category_name):
        cursor.execute("SELECT id_categoria FROM tbl_categorias WHERE chr_nombre = %s", (category_name,))
        row = cursor.fetchone()
        if row:
            print(f"Categoría encontrada: {category_name} (ID: {row['id_categoria']})")
            return row['id_categoria']
        else:
            cursor.execute("INSERT INTO tbl_categorias (chr_nombre, bit_activo) VALUES (%s, 1)", (category_name,))
            new_id = conn.insert_id()
            print(f"Categoría insertada: {category_name} (ID: {new_id})")
            return new_id

def get_identifier_type_id(cursor, type_name):
    cursor.execute("SELECT id_identificador_tipo FROM tbl_identificadores_tipos WHERE chr_nombre = %s", (type_name,))
    row = cursor.fetchone()
    if row:
        print(f"Tipo de identificador encontrado: {type_name} (ID: {row['id_identificador_tipo']})")
        return row['id_identificador_tipo']
    else:
            cursor.execute(
                "INSERT INTO tbl_identificadores_tipos (chr_nombre, chr_descripcion, bit_activo) VALUES (%s, %s, 1)",
                (type_name, type_name)
            )
            new_id = conn.insert_id()
            print(f"Tipo de identificador insertado: {type_name} (ID: {new_id})")
            return new_id

def download_image(url, save_dir='images'):
    pathlib.Path(save_dir).mkdir(parents=True, exist_ok=True)
    image_name = os.path.basename(urlparse(url).path)
    save_path = os.path.join(save_dir, image_name)
    if not os.path.exists(save_path):
        print(f"Descargando imagen: {url}")
        try:
            with urllib.request.urlopen(url, timeout=5) as response:
                image_data = response.read()
            with open(save_path, 'wb') as f:
                f.write(image_data)
            print(f"Imagen descargada: {url}")
        except Exception as e:
            print(f"Error al descargar la imagen (timeout o inaccesible): {url} - {e}")
            return None
    else:
        print(f"Imagen ya existe localmente: {save_path}")
    return save_path

def main():
    json_file = 'productos.json'
    if not os.path.exists(json_file):
        print(f"Archivo JSON no encontrado: {json_file}")
        return

    with open(json_file, 'r', encoding='utf-8') as f:
        try:
            products = json.load(f)
            print("Datos JSON decodificados correctamente.")
        except json.JSONDecodeError:
            print("Error al decodificar los datos JSON")
            return

    conn = connect_db()
    cursor = conn.cursor()

    try:
        # Preload identifier types
        identifier_types = {
            'SKU': get_identifier_type_id(cursor, 'SKU'),
            'Serial Number': get_identifier_type_id(cursor, 'Serial Number'),
            'UPC': get_identifier_type_id(cursor, 'UPC'),
            'Part Number': get_identifier_type_id(cursor, 'Part Number'),
        }
        print("Tipos de identificadores pre-cargados.")

        # Begin transaction
        # conn.start_transaction()  # Removed because PyMySQL does not have this method
        print("Transacción iniciada.")

        for product in products:
            category_name = product.get('categoria', 'Otros')
            category_id = get_category_id(cursor, category_name)

            # Insert product
            cursor.execute(
                "INSERT INTO tbl_productos (chr_nombre_prod, chr_desc_prod, id_categoria, int_activo, bit_es_combo) VALUES (%s, %s, %s, %s, 0)",
                (
                    product.get('nombre', ''),
                    product.get('descripcion_corta', ''),
                    category_id,
                    int(product.get('activo', 1))
                )
            )
            product_id = conn.insert_id()
            print(f"Producto insertado: {product.get('nombre', '')} (ID: {product_id})")

            # Insert identifiers
            if product.get('clave'):
                cursor.execute(
                    "INSERT INTO tbl_productos_identificadores (id_producto, id_identificador_tipo, chr_valor) VALUES (%s, %s, %s)",
                    (product_id, identifier_types['SKU'], product['clave'])
                )
                print(f"Identificador SKU insertado: {product['clave']}")
            if product.get('ean'):
                cursor.execute(
                    "INSERT INTO tbl_productos_identificadores (id_producto, id_identificador_tipo, chr_valor) VALUES (%s, %s, %s)",
                    (product_id, identifier_types['UPC'], product['ean'])
                )
                print(f"Identificador EAN insertado: {product['ean']}")
            if product.get('upc'):
                cursor.execute(
                    "INSERT INTO tbl_productos_identificadores (id_producto, id_identificador_tipo, chr_valor) VALUES (%s, %s, %s)",
                    (product_id, identifier_types['UPC'], product['upc'])
                )
                print(f"Identificador UPC insertado: {product['upc']}")
            if product.get('numParte'):
                cursor.execute(
                    "INSERT INTO tbl_productos_identificadores (id_producto, id_identificador_tipo, chr_valor) VALUES (%s, %s, %s)",
                    (product_id, identifier_types['Part Number'], product['numParte'])
                )
                print(f"Identificador Número de Parte insertado: {product['numParte']}")

            # Insert price
            if 'precio' in product:
                cursor.execute(
                    "INSERT INTO tbl_precios_productos (id_producto, fl_precio, dt_fecha_inicio) VALUES (%s, %s, NOW())",
                    (product_id, product['precio'])
                )
                print(f"Precio insertado: {product['precio']}")

            # Insert image
            if product.get('imagen'):
                image_path = download_image(product['imagen'])
                if image_path:
                    image_name = os.path.basename(image_path)
                    cursor.execute(
                        "INSERT INTO tbl_imagenes (chr_nombre, chr_ruta, chr_alt_text, bit_activo) VALUES (%s, %s, %s, 1)",
                        (image_name, image_path, product.get('nombre', ''))
                    )
                    image_id = conn.insert_id()
                    cursor.execute(
                        "INSERT INTO tbl_productos_imagenes (id_producto, id_imagen) VALUES (%s, %s)",
                        (product_id, image_id)
                    )
                    print(f"Imagen insertada y vinculada: {image_name} (ID: {image_id})")
                else:
                    print(f"No se pudo descargar o insertar la imagen para el producto: {product.get('nombre', '')}")

            # Insert specifications
            if 'especificaciones' in product and isinstance(product['especificaciones'], list):
                for spec in product['especificaciones']:
                    if 'tipo' in spec and 'valor' in spec:
                        cursor.execute(
                            "INSERT INTO tbl_productos_especificaciones (id_producto, chr_clave, chr_valor) VALUES (%s, %s, %s)",
                            (product_id, spec['tipo'], spec['valor'])
                        )
                        print(f"Especificación insertada: {spec['tipo']} = {spec['valor']}")

            # Insert promotions
            if 'promociones' in product and isinstance(product['promociones'], list):
                for promo in product['promociones']:
            if 'promocion' in promo:
                promo_text = ''
                if 'tipo' in promo:
                    promo_text += str(promo['tipo']) + ': '
                promo_text += str(promo['promocion'])
                cursor.execute(
                    "INSERT INTO tbl_productos_promociones (id_producto, chr_promocion) VALUES (%s, %s)",
                    (product_id, promo_text)
                )
                print(f"Promoción insertada: {promo_text}")

        # Commit transaction
        conn.commit()
        print("Importación completada con éxito.")

    except Exception as e:
        conn.rollback()
        print(f"Exception type: {type(e)}")
        print(f"Exception repr: {repr(e)}")
        print(f"La importación falló: {e}")

    finally:
        cursor.close()
        conn.close()

if __name__ == '__main__':
    main()
