import mysql
import numpy as np
import pandas as pd
from mysql.connector import (connection)
from mysql.connector import errorcode
from datetime import datetime
from dateutil.relativedelta import relativedelta

class DbConnection:
    _instance = None  # Variable de clase para almacenar la instancia única

    # Singleton Class
    def __new__(cls, *args, **kwargs):
        """
        Método especial para controlar la creación de la instancia (Singleton).
        """
        if not cls._instance:
            cls._instance = super(DbConnection, cls).__new__(cls, *args, **kwargs)
        return cls._instance

    def connect_to_mysql(self):
        try:
            return connection.MySQLConnection(
                host="127.0.0.1",
                user="root",
                password="root",
                database="proyectoia"
            )
        except mysql.connector.Error as err:
            if err.errno == errorcode.ER_ACCESS_DENIED_ERROR:
                print("Something is wrong with your user name or password")
            elif err.errno == errorcode.ER_BAD_DB_ERROR:
                print("Database does not exist")
            else:
                print(err)
            return None

class VitalSignsAnalyzer:
    def __init__(self):
        self.normal_ranges = {}

    def analyze(self, heart_rate, oxygen_saturation, temperature):
        """
        Analiza los datos proporcionados y determina los rangos normales basados en el percentil intercuartílico.

        Parámetros:
            heart_rate (list o np.array): Datos de frecuencia cardíaca.
            oxygen_saturation (list o np.array): Datos de oxigenación en la sangre.
            temperature (list o np.array): Datos de temperatura corporal.

        Devuelve:
            dict: Rango normal de cada signo vital.
        """

        def get_normal_range(data):
            q1 = np.percentile(data, 25)  # Primer cuartil
            q3 = np.percentile(data, 75)  # Tercer cuartil
            iqr = q3 - q1  # Rango intercuartílico
            lower_bound = q1 - 1.5 * iqr
            upper_bound = q3 + 1.5 * iqr
            return max(0, lower_bound), upper_bound

        self.normal_ranges['heart_rate'] = get_normal_range(heart_rate)
        self.normal_ranges['oxygen_saturation'] = get_normal_range(oxygen_saturation)
        self.normal_ranges['temperature'] = get_normal_range(temperature)

        return self.normal_ranges


def obtener_rangos_normales(id_paciente: int):
    """
    Obtiene los rangos normales de los signos vitales para diferentes estados.
    """
    try:
        estados = ["normal", "dormido", "reposo", "ejercicio"]
        rangos = []

        for estado in estados:
            resultado = obtener_rangos_estado(id_paciente, estado)
            if "error" in resultado:
                # Si ocurre un error en un estado, incluye el error en la respuesta
                rangos.append({estado: {"error": resultado["error"]}})
            else:
                rangos.append({estado: resultado})

        return {"status": "success", "ranges": rangos}

    except Exception as e:
        # Manejo de errores generales para problemas imprevistos
        return {"status": "error", "details": f"Error general: {str(e)}"}


def obtener_rangos_estado(id_paciente: int, estado: str):
    try:
        db_connection = DbConnection()
        connection = db_connection.connect_to_mysql()
        cursor = connection.cursor(dictionary=True)

        date = datetime.now()
        date_now = date + relativedelta(days=1)
        date_ago = date_now - relativedelta(months=6)

        # Ejecutar consulta SQL
        query = (
            '''
                WITH DailyRecords AS (
                    SELECT *, ROW_NUMBER() OVER (PARTITION BY CAST(timestamp AS DATE) ORDER BY RAND()) AS rn
                    FROM health_data
                    WHERE id_paciente = %s AND estado = %s AND timestamp BETWEEN %s AND %s
                )
                SELECT heart_rate, spo2, temperature
                FROM DailyRecords as dr
                WHERE (CAST(timestamp AS DATE), rn) IN (
                    SELECT CAST(timestamp AS DATE), rn
                    FROM DailyRecords
                    GROUP BY CAST(timestamp AS DATE), rn
                    HAVING rn <= 100
                )
                ORDER BY CAST(timestamp AS DATE), RAND();
            '''
        )
        cursor.execute(query, (id_paciente, estado, date_ago, date_now))
        records = cursor.fetchall()

        data = pd.DataFrame(records)

        if data.empty:
            raise ValueError("No se encontraron datos para el paciente especificado.")

        # Extraer columnas
        heart_rate_data = data['heart_rate'].to_numpy()
        oxygen_saturation_data = data['spo2'].to_numpy()
        temperature_data = data['temperature'].to_numpy()

        # Analizar los datos
        analyzer = VitalSignsAnalyzer()
        normal_ranges = analyzer.analyze(heart_rate_data, oxygen_saturation_data, temperature_data)

        return {
            sign: f"{ranges[0]:.2f} - {ranges[1]:.2f}"
            for sign, ranges in normal_ranges.items()
        }
    except mysql.connector.Error as db_err:
        return {"error": f"Error de base de datos: {db_err}"}
    except Exception as e:
        return {"error": f"Error general: {str(e)}"}
    finally:
        try:
            cursor.close()
            connection.close()
        except:
            pass
