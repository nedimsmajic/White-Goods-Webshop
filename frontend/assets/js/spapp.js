var app =$.spapp({
  defaultView: 'home',
  templateDir: './views/'
});

app.route({view:'home', load:'home.html'});
app.route({view:'products', load:'products.html'});
app.route({view:'login', load:'login.html'});
app.route({view:'about', load:'about.html'});

app.run();

console.log('SPApp works?')
