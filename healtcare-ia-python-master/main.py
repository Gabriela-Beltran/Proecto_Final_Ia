from fastapi import FastAPI
from pydantic import BaseModel
import app.prolog as prolog
import app.ia as ia

app = FastAPI()


# Modelo para recibir datos en el cuerpo de la solicitud
class AlertData(BaseModel):
    number: str
    message: str


@app.post("/alerta")
async def root(alert_data: AlertData):
    # Aquí llamas a tu función Prolog, pasándole los datos recibidos
    alert = prolog.enviar_alerta(alert_data.number, alert_data.message)
    return {"Alerta general": alert}


@app.get("/rangos_normales")
async def rangosNormales(id_paciente: int):

    ranges = ia.obtener_rangos_normales(id_paciente)
    if "error" in ranges:
        return {"status": "error", "details": ranges["error"]}
    return {"status": "success", "data": ranges}

if __name__ == "__main__":
    import uvicorn
    uvicorn.run("main:app", host="127.0.0.1", port=8000, reload=True, log_level="debug")
