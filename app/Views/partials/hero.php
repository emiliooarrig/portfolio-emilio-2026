<section id="top" class="hero" aria-labelledby="h-title">
    <div class="hero-bg" aria-hidden="true"></div>
    <div class="wrap hero-in">
        <div>
            <p class="eyebrow ff-m fade-up d-05"><span class="dot" aria-hidden="true"></span>Ingeniero en TI · Software &amp; Care Engineering</p>
            <h1 id="h-title" class="h-title ff-d">
                <span class="ln"><span class="d-15">Software que</span></span>
                <span class="ln"><span class="d-27">funciona.</span></span>
                <span class="ln"><span class="h-acc d-39">Soporte que no</span></span>
                <span class="ln"><span class="h-acc d-51">desaparece.</span></span>
            </h1>
            <p class="lead h-sub fade-up d-80">Soy Emilio Guzmán, ingeniero en TI. Desarrollo sistemas a la medida, los llevo a producción y acompaño a las personas que los usan todos los días.</p>
            <div class="cta-row fade-up d-95">
                <a class="btn btn-p" href="#proyectos"><span>Ver proyectos</span><?= icon('arrow-right') ?></a>
                <a class="btn btn-s" href="<?= e(asset((string) config('site.cv'))) ?>" download><span>Descargar CV</span><?= icon('download') ?></a>
            </div>
            <div class="now fade-up d-110">
                <span>Hoy:</span><b>Nokia</b><i aria-hidden="true"></i><b>Fovea Studio</b>
            </div>
        </div>

        <div class="hv" aria-hidden="true">
            <div class="ring"></div>
            <div class="orb"></div>
            <div class="sat"></div>
            <div class="term ff-m">
                <div class="t-bar"><i></i><i></i><i></i><span>~/emilio — zsh</span></div>
                <div class="t-body">
                    <span class="tl d-105"><span class="t-p">$</span> git push origin main</span>
                    <span class="tl t-ok d-130">✓ checks ok · rama main</span>
                    <span class="tl d-165"><span class="t-p">$</span> railway up --service la-llar</span>
                    <span class="tl t-ok d-190">✓ desplegado en producción</span>
                    <span class="tl d-225"><span class="t-p">$</span> tail -f soporte.log</span>
                    <span class="tl t-mut d-250">→ ticket cerrado · usuario contento</span>
                    <span class="tl d-285"><span class="t-p">$</span> <span class="caret"></span></span>
                </div>
            </div>
        </div>
    </div>
    <div class="cue ff-m" aria-hidden="true">Desliza<i></i></div>
</section>
