
// Schema definition
const usuarioSchema = new Schema({
    genero: {
        type: String,
        enum: generoValido
    },
    progreso: {
        type: String
    },
    experienciaLaboral: {
        type: String
    },
    licenciatura: {
        type: String
    },
    fechaNacimientoDia: {
        type: Number
    },
    fechaNacimientoMes: {
        type: Number
    },
    fechaNacimientoAnio: {
        type: Number
    },
    logro1: {
        type: String
    },
    logro2: {
        type: String
    },
    logro3: {
        type: String
    },
    habilidad1: {
        type: String
    },
    habilidad2: {
        type: String
    },
    habilidad3: {
        type: String
    },
    userRole: {
        type: String,
        required: true,
        default: "USER_PERSONAL",
        enum: rolesValidos
    },
    empleos: [{
        type: empleoSchema
    }],
    estudios: [{
        type: estudioSchema
    }],
    perfilVerificado: {
        type: String,
        required: true,
        default: "No verificado",
        enum: estatusPerfil
    },
    matricula: {
        type: String
    },
    anio_egreso: {
        type: Number
    },
    titulo: {
        type: String,
        default: "NO"
    },
    cedula: {
        type: String,
        default: "N/A"
    },
});

usuarioSchema.plugin(sanitizer);
// values uniques
usuarioSchema.plugin(uniqueValidator, {
    message: '{PATH} debe ser único!!'
});

usuarioSchema.methods.toJSON = function() {
    let usuario = this;
    let userObj = usuario.toObject();
    delete userObj.password;
    return userObj;
}

module.exports = mongoose.model("Usuario", usuarioSchema);