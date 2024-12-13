from pyswip import Prolog
from app.notification import sendWhatsappMessage

prolog = Prolog()
prolog.consult("conocimiento.pl")


def obtener_rango_ritmo_cardiaco(estado):
    query = f"rango_ritmo_cardiaco({estado}, MinFreq, MaxFreq)"
    resultados = list(prolog.query(query))
    for r in resultados:
        print(f"Rango de {estado}: {r['MinFreq']} - {r['MaxFreq']}")


def alerta_sueno_baja_frecuencia():
    result = list(prolog.query("alerta_sueno_baja_frecuencia(133, 'Israel')"))
    print(result)


def obtener_datos():
    resultados = list(prolog.query("signos_vitales(133, 'Israel', Frecuencia, Oxigenacion, Temperatura)"))
    for r in resultados:
        print(r)


def consulta_alerta(alerta, Id, Paciente):
    query = f"{alerta}({Id}, '{Paciente}')"
    resultado = list(prolog.query(query))
    alert = True if resultado else False

    # number = '6682318853'
    number = '6879998946'
    message = ('Alerta. \n'
               'Hola nyehehehe')

    if alert:
        sendWhatsappMessage(number, message)
    return alert


def enviar_alerta(number: str, message: str):
    try:
        sendWhatsappMessage(number, message)
        return True
    except:
        return False
