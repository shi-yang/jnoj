function changeTheme(theme) {
  if (theme === 'dark') {
    document.body.setAttribute('arco-theme', 'dark');
    document.documentElement.classList.add('dark');
  } else {
    document.body.removeAttribute('arco-theme');
    document.documentElement.classList.remove('dark');
  }
}

export default changeTheme;
