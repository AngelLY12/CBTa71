import { useState, useEffect } from 'react';
import AdminService from '../utils/adminService.js';

export default function UserFormModal({ isOpen, onClose, user = null, onSuccess }) {
  const [formData, setFormData] = useState({
    name: '',
    last_name: '',
    email: '',
    phone_number: '',
    birthdate: '',
    gender: 'hombre',
    curp: '',
    address: ['', '', ''], // [calle, colonia, ciudad]
    blood_type: '',
    status: 'activo',
    // Student details (opcional)
    career_id: '',
    n_control: '',
    semestre: '',
    group: '',
    workshop: '',
  });
  const [loading, setLoading] = useState(false);
  const [errors, setErrors] = useState({});
  const [isStudentMode, setIsStudentMode] = useState(false);

  // Cargar datos del usuario si estamos editando
  useEffect(() => {
    if (user) {
      setFormData({
        name: user.name || '',
        last_name: user.last_name || '',
        email: user.email || '',
        phone_number: user.phone_number || '',
        birthdate: user.birthdate || '',
        gender: user.gender || 'hombre',
        curp: user.curp || '',
        address: Array.isArray(user.address) ? user.address : ['', '', ''],
        blood_type: user.blood_type || '',
        status: user.status || 'activo',
        career_id: user.studentDetail?.career_id || '',
        n_control: user.studentDetail?.n_control || '',
        semestre: user.studentDetail?.semestre || '',
        group: user.studentDetail?.group || '',
        workshop: user.studentDetail?.workshop || '',
      });
      setIsStudentMode(!!user.studentDetail);
    } else {
      // Reset form for new user
      setFormData({
        name: '',
        last_name: '',
        email: '',
        phone_number: '',
        birthdate: '',
        gender: 'hombre',
        curp: '',
        address: ['', '', ''],
        blood_type: '',
        status: 'activo',
        career_id: '',
        n_control: '',
        semestre: '',
        group: '',
        workshop: '',
      });
      setIsStudentMode(false);
    }
    setErrors({});
  }, [user, isOpen]);

  const handleInputChange = (e) => {
    const { name, value } = e.target;
    setFormData(prev => ({
      ...prev,
      [name]: value,
    }));
    // Limpiar error del campo
    if (errors[name]) {
      setErrors(prev => ({ ...prev, [name]: null }));
    }
  };

  const handleAddressChange = (index, value) => {
    const newAddress = [...formData.address];
    newAddress[index] = value;
    setFormData(prev => ({
      ...prev,
      address: newAddress,
    }));
  };

  const validateForm = () => {
    const newErrors = {};

    if (!formData.name.trim()) newErrors.name = 'El nombre es requerido';
    if (!formData.last_name.trim()) newErrors.last_name = 'Los apellidos son requeridos';
    if (!formData.email.trim()) {
      newErrors.email = 'El email es requerido';
    } else if (!/\S+@\S+\.\S+/.test(formData.email)) {
      newErrors.email = 'El email no es válido';
    }
    if (!formData.curp.trim()) {
      newErrors.curp = 'El CURP es requerido';
    } else if (formData.curp.length !== 18) {
      newErrors.curp = 'El CURP debe tener 18 caracteres';
    }
    if (!formData.birthdate) newErrors.birthdate = 'La fecha de nacimiento es requerida';

    // Validaciones para modo estudiante
    if (isStudentMode && !user) {
      if (!formData.career_id) newErrors.career_id = 'La carrera es requerida';
      if (!formData.n_control) newErrors.n_control = 'El número de control es requerido';
      if (!formData.semestre) newErrors.semestre = 'El semestre es requerido';
    }

    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleSubmit = async (e) => {
    e.preventDefault();

    if (!validateForm()) {
      return;
    }

    setLoading(true);

    try {
      if (user) {
        // Actualizar usuario existente
        await AdminService.updateUser(user.id, formData);
        
        // Si hay datos de estudiante, actualizar también
        if (isStudentMode && user.studentDetail) {
          await AdminService.updateStudent(user.studentDetail.id, {
            career_id: formData.career_id,
            group: formData.group,
            workshop: formData.workshop,
          });
        }
      } else {
        // Crear nuevo usuario
        const response = await AdminService.registerUser({
          name: formData.name,
          last_name: formData.last_name,
          email: formData.email,
          phone_number: formData.phone_number,
          birthdate: formData.birthdate,
          gender: formData.gender,
          curp: formData.curp,
          address: formData.address,
          blood_type: formData.blood_type,
          status: formData.status,
        });

        // Si es modo estudiante y se creó el usuario, asociar detalles de estudiante
        if (isStudentMode && response.data?.user?.id) {
          await AdminService.attachStudent({
            user_id: response.data.user.id,
            career_id: formData.career_id,
            n_control: formData.n_control,
            semestre: formData.semestre,
            group: formData.group,
            workshop: formData.workshop,
          });
        }
      }

      onSuccess && onSuccess();
      onClose();
    } catch (err) {
      console.error('Error al guardar usuario:', err);
      if (err.errors) {
        setErrors(err.errors);
      } else {
        alert('Error: ' + (err.message || 'No se pudo guardar el usuario'));
      }
    } finally {
      setLoading(false);
    }
  };

  if (!isOpen) return null;

  return (
    <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div className="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
        {/* Header */}
        <div className="sticky top-0 bg-institucional text-white px-6 py-4 flex items-center justify-between">
          <h2 className="text-xl font-bold">
            {user ? '✏️ Editar Usuario' : '➕ Nuevo Usuario'}
          </h2>
          <button
            onClick={onClose}
            className="text-white hover:text-gray-200 text-2xl font-bold"
            disabled={loading}
          >
            ×
          </button>
        </div>

        {/* Form */}
        <form onSubmit={handleSubmit} className="p-6 space-y-6">
          {/* Modo Estudiante Toggle */}
          {!user && (
            <div className="bg-blue-50 border border-blue-200 rounded-lg p-4">
              <label className="flex items-center gap-2 cursor-pointer">
                <input
                  type="checkbox"
                  checked={isStudentMode}
                  onChange={(e) => setIsStudentMode(e.target.checked)}
                  className="rounded border-gray-300 text-institucional focus:ring-institucional"
                />
                <span className="font-medium text-blue-900">
                  👨‍🎓 Registrar como estudiante (incluir datos académicos)
                </span>
              </label>
            </div>
          )}

          {/* Datos Personales */}
          <div>
            <h3 className="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">
              📋 Datos Personales
            </h3>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">
                  Nombre(s) <span className="text-red-500">*</span>
                </label>
                <input
                  type="text"
                  name="name"
                  value={formData.name}
                  onChange={handleInputChange}
                  className={`w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-institucional ${
                    errors.name ? 'border-red-500' : 'border-gray-300'
                  }`}
                  required
                />
                {errors.name && <p className="text-red-500 text-xs mt-1">{errors.name}</p>}
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">
                  Apellidos <span className="text-red-500">*</span>
                </label>
                <input
                  type="text"
                  name="last_name"
                  value={formData.last_name}
                  onChange={handleInputChange}
                  className={`w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-institucional ${
                    errors.last_name ? 'border-red-500' : 'border-gray-300'
                  }`}
                  required
                />
                {errors.last_name && <p className="text-red-500 text-xs mt-1">{errors.last_name}</p>}
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">
                  Email <span className="text-red-500">*</span>
                </label>
                <input
                  type="email"
                  name="email"
                  value={formData.email}
                  onChange={handleInputChange}
                  className={`w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-institucional ${
                    errors.email ? 'border-red-500' : 'border-gray-300'
                  }`}
                  required
                />
                {errors.email && <p className="text-red-500 text-xs mt-1">{errors.email}</p>}
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">
                  CURP <span className="text-red-500">*</span>
                </label>
                <input
                  type="text"
                  name="curp"
                  value={formData.curp}
                  onChange={handleInputChange}
                  maxLength={18}
                  className={`w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-institucional uppercase ${
                    errors.curp ? 'border-red-500' : 'border-gray-300'
                  }`}
                  required
                />
                {errors.curp && <p className="text-red-500 text-xs mt-1">{errors.curp}</p>}
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">
                  Teléfono
                </label>
                <input
                  type="tel"
                  name="phone_number"
                  value={formData.phone_number}
                  onChange={handleInputChange}
                  className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-institucional"
                />
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">
                  Fecha de Nacimiento <span className="text-red-500">*</span>
                </label>
                <input
                  type="date"
                  name="birthdate"
                  value={formData.birthdate}
                  onChange={handleInputChange}
                  className={`w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-institucional ${
                    errors.birthdate ? 'border-red-500' : 'border-gray-300'
                  }`}
                  required
                />
                {errors.birthdate && <p className="text-red-500 text-xs mt-1">{errors.birthdate}</p>}
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">
                  Género
                </label>
                <select
                  name="gender"
                  value={formData.gender}
                  onChange={handleInputChange}
                  className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-institucional"
                >
                  <option value="hombre">Hombre</option>
                  <option value="mujer">Mujer</option>
                  <option value="otro">Otro</option>
                </select>
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">
                  Tipo de Sangre
                </label>
                <select
                  name="blood_type"
                  value={formData.blood_type}
                  onChange={handleInputChange}
                  className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-institucional"
                >
                  <option value="">Seleccionar...</option>
                  <option value="O+">O+</option>
                  <option value="O-">O-</option>
                  <option value="A+">A+</option>
                  <option value="A-">A-</option>
                  <option value="B+">B+</option>
                  <option value="B-">B-</option>
                  <option value="AB+">AB+</option>
                  <option value="AB-">AB-</option>
                </select>
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">
                  Estado
                </label>
                <select
                  name="status"
                  value={formData.status}
                  onChange={handleInputChange}
                  className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-institucional"
                >
                  <option value="activo">Activo</option>
                  <option value="baja-temporal">Baja Temporal</option>
                  <option value="baja">Baja</option>
                </select>
              </div>
            </div>

            {/* Dirección */}
            <div className="mt-4">
              <label className="block text-sm font-medium text-gray-700 mb-2">
                Dirección
              </label>
              <div className="space-y-2">
                <input
                  type="text"
                  placeholder="Calle y número"
                  value={formData.address[0]}
                  onChange={(e) => handleAddressChange(0, e.target.value)}
                  className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-institucional"
                />
                <input
                  type="text"
                  placeholder="Colonia"
                  value={formData.address[1]}
                  onChange={(e) => handleAddressChange(1, e.target.value)}
                  className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-institucional"
                />
                <input
                  type="text"
                  placeholder="Ciudad y Estado"
                  value={formData.address[2]}
                  onChange={(e) => handleAddressChange(2, e.target.value)}
                  className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-institucional"
                />
              </div>
            </div>
          </div>

          {/* Datos de Estudiante */}
          {isStudentMode && (
            <div>
              <h3 className="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">
                🎓 Datos Académicos
              </h3>
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">
                    ID Carrera <span className="text-red-500">*</span>
                  </label>
                  <input
                    type="number"
                    name="career_id"
                    value={formData.career_id}
                    onChange={handleInputChange}
                    className={`w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-institucional ${
                      errors.career_id ? 'border-red-500' : 'border-gray-300'
                    }`}
                    required={isStudentMode && !user}
                  />
                  {errors.career_id && <p className="text-red-500 text-xs mt-1">{errors.career_id}</p>}
                </div>

                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">
                    Número de Control <span className="text-red-500">*</span>
                  </label>
                  <input
                    type="text"
                    name="n_control"
                    value={formData.n_control}
                    onChange={handleInputChange}
                    className={`w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-institucional ${
                      errors.n_control ? 'border-red-500' : 'border-gray-300'
                    }`}
                    required={isStudentMode && !user}
                    disabled={user && user.studentDetail}
                  />
                  {errors.n_control && <p className="text-red-500 text-xs mt-1">{errors.n_control}</p>}
                </div>

                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">
                    Semestre <span className="text-red-500">*</span>
                  </label>
                  <input
                    type="number"
                    name="semestre"
                    value={formData.semestre}
                    onChange={handleInputChange}
                    min="1"
                    max="12"
                    className={`w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-institucional ${
                      errors.semestre ? 'border-red-500' : 'border-gray-300'
                    }`}
                    required={isStudentMode && !user}
                    disabled={user && user.studentDetail}
                  />
                  {errors.semestre && <p className="text-red-500 text-xs mt-1">{errors.semestre}</p>}
                </div>

                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">
                    Grupo
                  </label>
                  <input
                    type="text"
                    name="group"
                    value={formData.group}
                    onChange={handleInputChange}
                    className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-institucional"
                  />
                </div>

                <div className="md:col-span-2">
                  <label className="block text-sm font-medium text-gray-700 mb-1">
                    Taller
                  </label>
                  <input
                    type="text"
                    name="workshop"
                    value={formData.workshop}
                    onChange={handleInputChange}
                    className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-institucional"
                  />
                </div>
              </div>
            </div>
          )}

          {/* Botones */}
          <div className="flex justify-end gap-3 pt-4 border-t">
            <button
              type="button"
              onClick={onClose}
              disabled={loading}
              className="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors disabled:opacity-50"
            >
              Cancelar
            </button>
            <button
              type="submit"
              disabled={loading}
              className="px-6 py-2 bg-institucional text-white rounded-lg hover:bg-institucional/90 transition-colors disabled:opacity-50 flex items-center gap-2"
            >
              {loading ? (
                <>
                  <svg className="animate-spin h-5 w-5" viewBox="0 0 24 24">
                    <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" fill="none"></circle>
                    <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  Guardando...
                </>
              ) : (
                <>💾 {user ? 'Actualizar' : 'Crear'} Usuario</>
              )}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}
