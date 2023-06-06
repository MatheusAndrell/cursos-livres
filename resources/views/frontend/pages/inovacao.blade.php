@extends('frontend.layouts.app' . config('theme_layout'))

@section('title', $page->meta_title ? $page->meta_title : app_name())
@section('meta_description', $page->meta_description ? $page->meta_description : '')
@section('meta_keywords', $page->meta_keywords ? $page->meta_keywords : app_name())


@section('content')

    <!-- Start of prallax
                     ============================================= -->
    <section class="galaxy">
        <img src="/img/stars.png" id="stars">
        <img src="/img/moon.png" id="moon">
        <img src="/img/mountains_behind.png" id="mountains_behind">
        <h2 id="text">{{ env('APP_NAME') }}</h2>
        <img src="/img/mountains_front.png" id="mountains_front">
    

        <script>
            let stars = document.getElementById('stars');
            let moon = document.getElementById('moon');
            let mountains_behind = document.getElementById('mountains_behind');
            let mountains_front = document.getElementById('mountains_front');
            let text = document.getElementById('text');

            window.addEventListener('scroll', function() {
                let value = window.scrollY;
                stars.style.left = value * 0.25 + 'px';
                moon.style.top = value * 1.25 + 'px';
                mountains_behind.style.top = value * 0.5 + 'px';
                mountains_front.style.top = value * 0 + 'px';
                text.style.marginRight = value * 4 + 'px';
                text.style.marginTop = value * 1.5 + 'px';
            })
        </script>
    </section>
    <!-- End of prallax
                    ============================================= -->
    <!-- Start of about-page section
                    ============================================= -->
    <section id="about-page" class="about-page-section-galaxy">
        <div class="container">
            <div class="row">
                <div class="@if ($page->sidebar == 1) col-md-9 @else col-md-12 @endif ">
                    <div class="about-us-content-item">

                                <!-- /gallery -->

                        <div class="about-text-item-galaxy">
                            <div class="section-title-2-galaxyy  headline text-left">
                                <h2>{{ $page->title }}</h2>
                            </div>
                            {!! $page->content !!}
                        </div>
                        <!-- /about-text -->
                    </div>
                </div>
                @if ($page->sidebar == 1)
                    @include('frontend.layouts.partials.right-sidebar')
                @endif
            </div>
        </div>
    </section>
    <!-- End of about-page section
                            ============================================= -->
    <!-- Start of container-card
                            ============================================= -->
    <section>
        <div class="container-cardw stars">
            <div class="cardw">
                <div class="img">
                    <span>Profissionais capacitados</span>
                </div>
                <div class="content">

                    <p class="desc-card">Uma das vantagens de escolher o nosso curso é que ele oferece uma formação completa
                        e
                        atualizada, com foco nas demandas e tendências do mercado de trabalho. Os profissionais que se
                        formam em nosso curso estão aptos a lidar com os desafios do mundo corporativo e a desenvolver
                        soluções criativas e eficientes para os problemas das empresas.</p>
                </div>
                <div class="arrow">
                    <span>&#8673;</span>
                </div>
            </div>
            <div class="cardw">
                <div class="img">
                    <span>Inovação e tecnologia</span>
                </div>
                <div class="content">

                    <p class="desc-card">Outra vantagem é a ênfase na inovação e tecnologia, que são fundamentais para o
                        sucesso das organizações nos dias de hoje. Os alunos do nosso curso têm acesso a recursos
                        tecnológicos
                        de última geração, além de estarem imersos em um ambiente de inovação e criatividade, que estimula a
                        criação de soluções inovadoras.</p>
                </div>
                <div class="arrow">
                    <span>&#8673;</span>
                </div>
            </div>
            <div class="cardw">
                <div class="img">
                    <span>Excelência acadêmica</span>
                </div>
                <div class="content">

                    <p class="desc-card">Uma terceira vantagem é a possibilidade de formar uma rede de contatos de grande
                        valor
                        para a sua carreira. Durante o curso, os alunos têm a oportunidade de interagir com colegas,
                        professores e profissionais do mercado, criando laços e estabelecendo conexões que podem abrir
                        portas para oportunidades de emprego e parcerias futuras.</p>
                </div>
                <div class="arrow">
                    <span>&#8673;</span>
                </div>
            </div>
        </div>
    </section>
    <!-- End of container-card
                        ============================================= -->
    <!-- Start of element-section
                        ============================================= -->
    <section class="element-section">
        <div class="element">
            <div class="title-element">
                <p class="p-element">O que temos de especial que ninguém mais tem</p>
            </div>
            <div class="cards-container">
                <div class="element-card">
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                    <div class="card-content">
                        <h2>Conteúdo de qualidade</h2>
                        <p> Nosso site de curso oferece um conteúdo de qualidade, que é projetado para ser fácil de entender
                            e aplicar na vida real. Isso significa que os alunos poderão aprender de forma eficiente e
                            aplicar o que aprenderam em seu trabalho ou vida pessoal.</p>
                    </div>
                </div>
                <div class="element-card">
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                    <div class="card-content">
                        <h2>Professores experientes</h2>
                        <p> Nossos professores são altamente experientes e qualificados em suas áreas de especialização.
                            Eles
                            têm uma ampla gama de habilidades, experiência prática e conhecimentos para compartilhar com os
                            alunos.</p>
                    </div>
                </div>
                <div class="element-card">
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                    <div class="card-content">
                        <h2>Flexibilidade de horários</h2>
                        <p> Nosso site de curso oferece flexibilidade de horários, permitindo que os alunos estudem em seu
                            próprio ritmo e horário conveniente. Isso é especialmente útil para aqueles que têm trabalhos
                            diários ou outras responsabilidades que tornam difícil seguir uma programação rígida.</p>
                    </div>
                </div>
            </div>
    </section>
    <!-- End of element-section
                       ============================================= -->
    <!-- Start of subscribe
                       ============================================= -->
    <section class="container-form">
        <section>
            <div class="content-form">
                <div class="form-title">
                    <h2> Matricule-se agora </h2>
                </div>
                <div class="form-x">
                    <h2> 12x </h2>
                </div>
                <div class="form-price">
                    <h2> 999 </h2>
                </div>
                <div class="form-2">
                    <h2> ,99 </h2>
                </div>
                <div class="form-info">
                    <h2> ou {{$appCurrency['symbol'].' '. $bundle->price }} à vista </h2>
                </div>
                <div class="container-form-button">
                    <form action="{{ route('cart.checkout') }}" method="POST">
                        @csrf
                        <input type="hidden" name="bundle_id" value="{{ $bundle->id }}" />
                        <input type="hidden" name="amount" value="{{ $bundle->price }}" />
                        <button class="form-button-platinum" href="#"> <span><b>QUERO PAGAR COM CARTÃO
                                    <br>
                                    DE CRÉDITO</b> - EM ATÉ 12X</span> </button>
                    </form>
                    <form action="{{ route('cart.checkout') }}" method="POST">
                        @csrf
                        <input type="hidden" name="bundle_id" value="{{ $bundle->id }}" />
                        <input type="hidden" name="amount" value="{{ $bundle->price }}" />
                        <button class="form-button-platinum2" href="#"> <span><b> QUERO PAGAR COM BOLETO
                                    <br>
                                    OU PIX </b> - À VISTA</span></button>
                    </form>
                </div>
            </div>
        </section>
    </section>
    <!-- End of subscribe
                       ============================================= -->
    <!-- Start of course
                       ============================================= -->
    <section class="ofcourse-galaxy">
        <div class="titlecourse-galaxy">
            <h2> O que você vai aprender?</h2>
        </div>
        <div class="modules-galaxy">
            <ul>
                <li>
                    <button onclick="toggleSubmodules(1)">Módulo 01 - Fundamentos do Front-end </button>
                    <ul class="submodule-animated" style="display:none">
                        <li>Aula &nbsp 1: Introdução ao desenvolvimento Front-end</li>
                        <li>Aula &nbsp 2: HTML - Estruturação de páginas web</li>
                        <li>Aula &nbsp 3: CSS - Estilização de páginas web</li>
                        <li>Aula &nbsp 4: JavaScript - Programação básica</li>
                    </ul>
                </li>
                <li>
                    <button onclick="toggleSubmodules(2)">Módulo 02 - Avançando no Front-end</button>
                    <ul class="submodule-animated" style="display:none">
                        <li>Aula &nbsp 1: Frameworks CSS</li>
                        <li>Aula &nbsp 2: JavaScript - Programação intermediária</li>
                        <li>Aula &nbsp 3: jQuery - Biblioteca JavaScript</li>
                        <li>Aula &nbsp 4: Responsividade e Design Responsivo</li>
                    </ul>
                </li>
                <li>
                    <button onclick="toggleSubmodules(3)">Módulo 03 - Frameworks Front-end </button>
                    <ul class="submodule-animated" style="display:none">
                        <li>Aula &nbsp 1: Introdução aos Frameworks Front-end</li>
                        <li>Aula &nbsp 2: Angular - Framework da Google</li>
                        <li>Aula &nbsp 3: React - Biblioteca de Componentes</li>
                        <li>Aula &nbsp 4: Vue - Framework progressivo</li>
                        <li>Aula &nbsp 4: Integração de API's</li>
                    </ul>
                </li>
                <li>
                    <button onclick="toggleSubmodules(4)">Módulo 04 - Projetos e Exercícios finais</button>
                    <ul class="submodule-animated" style="display:none">
                        <li>Aula &nbsp 1: Projeto Final - Criação de um Site</li>
                        <li>Aula &nbsp 2: Desafios para praticar </li>
                    </ul>
                </li>
            </ul>
            <script>
                function toggleSubmodules(moduleIndex) {
                    var submodules = document.getElementsByClassName('submodule-' + moduleIndex);
                    for (var i = 0; i < submodules.length; i++) {
                        submodules[i].classList.toggle('submodule-animated');
                    }
                }
            </script>
    </section>


    <!-- End of course
                     ============================================= -->

    <!-- Start Contacts
                     ============================================= -->
        <section class="contact">
            <div class="contact-container">
                <span class="big-circle"></span>
                <img src="/img/shape.png" class="square" alt="" />
                <div class="form">
                  <div class="contact-infoo">
                    <h3 class="title">Tem alguma dúvida?</h3>
                    <p class="text">
                      Lorem ipsum dolor sit amet consectetur adipisicing elit. Saepe
                      dolorum adipisci recusandae praesentium dicta!
                    </p>
          
                    <div class="info">
                      <div class="information">
                        <img src="/img/location.png" class="icon" alt="" />
                        <p>92 Cherry Drive Uniondale, NY 11553</p>
                      </div>
                      <div class="information">
                        <img src="/img/email.png" class="icon" alt="" />
                        <p>lorem@ipsum.com</p>
                      </div>
                      <div class="information">
                        <img src="/img/phone.png" class="icon" alt="" />
                        <p>123-456-789</p>
                      </div>
                    </div>
          
                    <div class="social-mediaa">
                      <p>Nossas redes-sociais :</p>
                      <div class="social-iconss">
                        <a href="#">
                          <i class="fab fa-facebook-f sm"></i>
                        </a>
                        <a href="#">
                          <i class="fab fa-twitter sm"></i>
                        </a>
                        <a href="#">
                          <i class="fab fa-instagram sm"></i>
                        </a>
                        <a href="#">
                          <i class="fab fa-linkedin-in sm"></i>
                        </a>
                      </div>
                    </div>
                  </div>
          
                  <div class="contact-form">
                    <span class="circle one"></span>
                    <span class="circle two"></span>
          
                    <form action="cc" autocomplete="off">
                      <h3 class="title">Contate-nos</h3>
                      <div class="input-container">
                        <input type="text" name="name" class="input" />
                        <label for="">Nome</label>
                        <span>Nome</span>
                      </div>
                      <div class="input-container">
                        <input type="email" name="email" class="input" />
                        <label for="">Email</label>
                        <span>Email</span>
                      </div>
                      <div class="input-container">
                        <input type="tel" name="phone" class="input" />
                        <label for="">Telefone</label>
                        <span>Telefone</span>
                      </div>
                      <div class="input-container textarea">
                        <textarea name="message" class="input"></textarea>
                        <label for="">Mensagem</label>
                        <span>Mensagem</span>
                      </div>
                      <input type="submit" value="Enviar" class="btnn"/>
                    </form>
                  </div>
                </div>
              </div>
              <script src="/js/form.js"></script>
        </section>

    <!-- End Contacts
                     ============================================= -->
    <!-- Start Footer
                     ============================================= -->
                     <footer class="galaxy-footer">
                        <div id="footer_content">
                            <div id="footer_contacts">
                                <h1>Logo</h1>
                                <p>It's all about your dreams.</p>
                
                                <div id="footer_social_media">
                                    <a href="#" class="footer-link" id="instagram">
                                        <i class="fab fa-instagram"></i>
                                    </a>
                
                                    <a href="#" class="footer-link" id="facebook">
                                        <i class="fab fa-facebook-f"></i>
                                    </a>
                
                                    <a href="#" class="footer-link" id="whatsapp">
                                        <i class="fab fa-whatsapp"></i>
                                    </a>
                                </div>
                            </div>
                            
                            <ul class="footer-list">
                                <li>
                                    <h3>Blog</h3>
                                </li>
                                <li>
                                    <a href="#" class="footer-link">Tech</a>
                                </li>
                                <li>
                                    <a href="#" class="footer-link">Adventures</a>
                                </li>
                                <li>
                                    <a href="#" class="footer-link">Music</a>
                                </li>
                            </ul>
                
                            <ul class="footer-list">
                                <li>
                                    <h3>Products</h3>
                                </li>
                                <li>
                                    <a href="#" class="footer-link">App</a>
                                </li>
                                <li>
                                    <a href="#" class="footer-link">Desktop</a>
                                </li>
                                <li>
                                    <a href="#" class="footer-link">Cloud</a>
                                </li>
                            </ul>
                
                            <div id="footer_subscribe">
                                <h3>Subscribe</h3>
                
                                <p>
                                    Enter your e-mail to get notified about
                                    our news solutions
                                </p>
                
                                <div id="input_group">
                                    <input type="email" id="email">
                                    <button>
                                        <i class="fas fa-mail-bulk"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                
                        <div id="footer_copyright">
                            &#169
                            2023 all rights reserved
                        </div>
                    </footer>
    <!-- End Footer
                     ============================================= -->
