import pymysql

def binary_to_int(binary_id):
    # Convert binary(16) UUID to int by taking first 4 bytes (to match int size)
    if binary_id is None:
        return None
    if isinstance(binary_id, int):
        return binary_id
    return int.from_bytes(binary_id[:2], byteorder='big', signed=False)

def binary_to_double(binary_id):
    # Convert binary(16) UUID to double by taking first 8 bytes and interpreting as float64
    import struct
    if binary_id is None:
        return None
    if isinstance(binary_id, float):
        return binary_id
    # Use struct to unpack 8 bytes as double
    return struct.unpack('>d', binary_id[:8])[0]

def binary_to_bigint(binary_id):
    # Convert binary(16) UUID to 64-bit int by taking first 8 bytes
    if binary_id is None:
        return None
    return int.from_bytes(binary_id[:4], byteorder='big', signed=False)

def import_table(src_cursor, tgt_cursor, src_table, tgt_table, id_col, name_col, tgt_id_col, tgt_name_col, id_type='int'):
    src_cursor.execute(f"SELECT id, {name_col} FROM {src_table}")
    rows = src_cursor.fetchall()

    insert_sql = f"""
        INSERT INTO {tgt_table} ({tgt_id_col}, {tgt_name_col})
        VALUES (%s, %s)
        ON DUPLICATE KEY UPDATE {tgt_name_col} = VALUES({tgt_name_col})
    """

    count = 0
    for row in rows:
        # Convert binary id to appropriate type before inserting
        if id_type == 'int':
            row_id = binary_to_int(row['id'])
        elif id_type == 'bigint':
            row_id = binary_to_bigint(row['id'])
        elif id_type == 'double':
            row_id = binary_to_double(row['id'])
        else:
            row_id = row['id']
        row_name = row[name_col]
        tgt_cursor.execute(insert_sql, (row_id, row_name))
        count += 1

    return count

def import_catalog_postal_codes(src_cursor, tgt_cursor):
    src_cursor.execute("SELECT id, state_id, municipality_id, settlement_id, city_id, postal_code_id, zone_id, settlement_type_id, datetime_id FROM catalog_postal_codes")
    rows = src_cursor.fetchall()

    insert_sql = """
        INSERT INTO tbl_catalogo_codigos_postales
        (id, state_id, municipality_id, settlement_id, city_id, postal_code_id, settlement_type_id, datetime_id)
        VALUES (%s, %s, %s, %s, %s, %s, %s, %s)
        ON DUPLICATE KEY UPDATE
            state_id = VALUES(state_id),
            municipality_id = VALUES(municipality_id),
            settlement_id = VALUES(settlement_id),
            city_id = VALUES(city_id),
            postal_code_id = VALUES(postal_code_id),
            settlement_type_id = VALUES(settlement_type_id),
            datetime_id = VALUES(datetime_id)
    """

    count = 0
    for row in rows:
        row_id = binary_to_bigint(row['id'])
        state_id = binary_to_int(row['state_id']) if row['state_id'] else None
        municipality_id = binary_to_int(row['municipality_id']) if row['municipality_id'] else None
        settlement_id = binary_to_int(row['settlement_id']) if row['settlement_id'] else None
        city_id = binary_to_bigint(row['city_id']) if row['city_id'] else None
        postal_code_id = binary_to_int(row['postal_code_id']) if row['postal_code_id'] else None
        settlement_type_id = binary_to_int(row['settlement_type_id']) if row['settlement_type_id'] else None
        datetime_id = binary_to_int(row['datetime_id']) if row['datetime_id'] else None

        tgt_cursor.execute(insert_sql, (row_id, state_id, municipality_id, settlement_id, city_id, postal_code_id, settlement_type_id, datetime_id))
        count += 1

    return count

def main():
    # Source DB connection (db_codigos_postales)
    src_conn = pymysql.connect(
        host='localhost',
        user='root',
        password='t4a2x0a6',
        database='db_codigos_postales',
        charset='utf8mb4',
        cursorclass=pymysql.cursors.DictCursor
    )

    # Target DB connection (db_online_shop)
    tgt_conn = pymysql.connect(
        host='localhost',
        user='root',
        password='t4a2x0a6',
        database='db_online_shop',
        charset='utf8mb4',
        cursorclass=pymysql.cursors.DictCursor
    )

    try:
        with src_conn.cursor() as src_cursor, tgt_conn.cursor() as tgt_cursor:
            total_imported = 0

            # Import city (id_ciudad is double in target)
            count = import_table(src_cursor, tgt_cursor, 'city', 'tbl_ciudades', 'id', 'name', 'id_ciudad', 'chr_nombre', id_type='int')
            print(f"Imported {count} cities.")
            total_imported += count

            # Import municipality
            count = import_table(src_cursor, tgt_cursor, 'municipality', 'tbl_municipios', 'id', 'name', 'id_municipio', 'chr_nombre_municipio', id_type='int')
            print(f"Imported {count} municipalities.")
            total_imported += count

            # Import postal_code
            count = import_table(src_cursor, tgt_cursor, 'postal_code', 'tbl_codigos_postales', 'id', 'code', 'id_codigo_postal', 'chr_codigo_postal', id_type='int')
            print(f"Imported {count} postal codes.")
            total_imported += count

            # Import settlement
            count = import_table(src_cursor, tgt_cursor, 'settlement', 'tbl_colonias', 'id', 'name', 'id_colonia', 'chr_nombre_colonia', id_type='int')
            print(f"Imported {count} settlements.")
            total_imported += count

            # Import settlement_type (id_tipo_asentamiento is bigint)
            count = import_table(src_cursor, tgt_cursor, 'settlement_type', 'tbl_tipo_asentamiento', 'id', 'name', 'id_tipo_asentamiento', 'chr_nombre_tipo_asentamiento', id_type='bigint')
            print(f"Imported {count} settlement types.")
            total_imported += count

            # Import states
            count = import_table(src_cursor, tgt_cursor, 'state', 'tbl_estados', 'id', 'name', 'id_estado', 'chr_nombre', id_type='int')
            print(f"Imported {count} states.")
            total_imported += count

            # Import catalog_postal_codes
            count = import_catalog_postal_codes(src_cursor, tgt_cursor)
            print(f"Imported {count} catalog postal codes.")
            total_imported += count

            tgt_conn.commit()
            print(f"Total imported rows: {total_imported}")

    finally:
        src_conn.close()
        tgt_conn.close()

if __name__ == "__main__":
    main()
