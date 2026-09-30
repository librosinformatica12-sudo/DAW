function showForm(which){
  const isLogin = which === 'login';
  document.getElementById('loginView').style.display = isLogin ? 'block' : 'none';
  document.getElementById('registerView').style.display = isLogin ? 'none' : 'block';
  document.getElementById('tabLogin').classList.toggle('active', isLogin);
  document.getElementById('tabRegister').classList.toggle('active', !isLogin);
  document.getElementById('status').textContent = '';
}

function getUsers(){
  try{
    return JSON.parse(localStorage.getItem('demo_users') || '{}');
  }catch(e){
    return {};
  }
}
function saveUsers(users){
  try{
    localStorage.setItem('demo_users', JSON.stringify(users));
  }catch(e){ /* almacenamiento no disponible, seguimos sin guardar */ }
}

function setStatus(text, ok){
  const el = document.getElementById('status');
  el.textContent = text;
  el.className = 'status ' + (ok ? 'ok' : 'err');
}

function handleRegister(e){
  e.preventDefault();
  const name = document.getElementById('regName').value.trim();
  const email = document.getElementById('regEmail').value.trim().toLowerCase();
  const pw = document.getElementById('regPassword').value;
  const pw2 = document.getElementById('regPassword2').value;
  const msg = document.getElementById('registerMsg');
  msg.textContent = '';

  if(pw.length < 8){
    msg.textContent = 'La contraseña debe tener al menos 8 caracteres.';
    return false;
  }
  if(pw !== pw2){
    msg.textContent = 'Las contraseñas no coinciden.';
    return false;
  }

  const users = getUsers();
  if(users[email]){
    msg.textContent = 'Ya existe una cuenta con ese correo.';
    return false;
  }

  users[email] = { name, password: pw };
  saveUsers(users);
  setStatus('Cuenta creada. Ahora puedes iniciar sesión.', true);
  document.getElementById('registerForm').reset();
  showForm('login');
  return false;
}

function handleLogin(e){
  e.preventDefault();
  const email = document.getElementById('loginEmail').value.trim().toLowerCase();
  const pw = document.getElementById('loginPassword').value;
  const msg = document.getElementById('loginMsg');
  msg.textContent = '';

  const users = getUsers();
  const user = users[email];
  if(!user || user.password !== pw){
    msg.textContent = 'Correo o contraseña incorrectos.';
    return false;
  }

  setStatus('Bienvenido, ' + user.name + '.', true);
  return false;
}
