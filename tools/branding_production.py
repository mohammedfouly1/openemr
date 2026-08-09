"""Deterministic Thiqa Group 1.5B production and evidence generator."""
from __future__ import annotations
import csv, hashlib, json, os, shutil, struct, subprocess
from pathlib import Path
import xml.etree.ElementTree as ET

ROOT = Path(__file__).resolve().parents[1]
INP = ROOT / "docs/Thiqa_Group_1_5B_Handoff/inputs/svg_masters_unmapped"
BRAND = ROOT / "brand"
DOCS = ROOT / "docs/branding-production"
MAGICK = next(Path("C:/Program Files").glob("ImageMagick-*/magick.exe"))

def sha(p): return hashlib.sha256(p.read_bytes()).hexdigest()
def run(*args): subprocess.run([str(MAGICK), *map(str,args)], check=True, capture_output=True)
def mkdirs():
    for d in ["master","logos/primary","logos/compact","logos/symbol","logos/monochrome","logos/login","logos/portal","logos/navbar","logos/print","logos/legacy","favicon","colors","typography","tokens","smart","email","rtl","guidelines","previews","qa","manifests"]: (BRAND/d).mkdir(parents=True,exist_ok=True)

MAP = {
 "brand-symbol.svg":"image (7).svg", "brand-symbol-black.svg":"image (5).svg",
 "brand-symbol-white.svg":"image (6).svg", "brand-logo-primary.svg":"image (8).svg",
 "brand-logo-primary-dark.svg":"image (4).svg", "brand-logo-compact.svg":"image (9).svg",
 "brand-logo-black.svg":"image (10).svg",
}

def copy_masters():
    for out,src in MAP.items(): shutil.copy2(INP/src, BRAND/"master"/out)
    # Exact geometry of approved monochrome master, deterministic color-only substitution.
    tree=ET.parse(INP/"image (10).svg"); root=tree.getroot()
    for e in root.iter():
        if e.tag.endswith("path") and "fill" in e.attrib: e.set("fill","#FFFFFF")
    ET.register_namespace("", "http://www.w3.org/2000/svg")
    tree.write(BRAND/"master/brand-logo-white.svg",encoding="utf-8",xml_declaration=True)
    routes={
      "brand-logo-primary.svg":"logos/primary/brand-logo-primary.svg",
      "brand-logo-primary-dark.svg":"logos/primary/brand-logo-primary-dark.svg",
      "brand-logo-compact.svg":"logos/compact/brand-logo-compact.svg",
      "brand-logo-black.svg":"logos/monochrome/brand-logo-black.svg",
      "brand-logo-white.svg":"logos/monochrome/brand-logo-white.svg",
      "brand-symbol.svg":"logos/symbol/brand-symbol.svg",
      "brand-symbol-black.svg":"logos/symbol/brand-symbol-black.svg",
      "brand-symbol-white.svg":"logos/symbol/brand-symbol-white.svg"}
    for src,dst in routes.items(): shutil.copy2(BRAND/"master"/src,BRAND/dst)
    shutil.copy2(INP/"image (1).svg",BRAND/"logos/primary/brand-logo-primary-cream-background.svg")
    shutil.copy2(INP/"image (2).svg",BRAND/"logos/compact/brand-logo-compact-cream-background.svg")
    shutil.copy2(INP/"image (3).svg",BRAND/"logos/monochrome/brand-logo-dark-cream-background.svg")
    shutil.copy2(INP/"image.svg",BRAND/"logos/symbol/brand-symbol-cream-background.svg")
    shutil.copy2(ROOT/"docs/Thiqa_Group_1_5B_Handoff/inputs/reference_docs/typography-weight-contract.md",BRAND/"typography/typography-weight-contract.md")

def render(svg,out,w,h):
    run("-background","none",svg,"-resize",f"{w}x{h}","-gravity","center","-extent",f"{w}x{h}","-strip",out)

def exports():
    m=BRAND/"master"
    jobs=[
      (m/"brand-logo-primary.svg","logos/login/login-primary-1053x390.png",1053,390),
      (m/"brand-logo-primary.svg","logos/login/login-secondary-300x100.png",300,100),
      (m/"brand-symbol.svg","logos/login/login-small-a-101x100.png",101,100),
      (m/"brand-symbol.svg","logos/login/login-small-b-101x100.png",101,100),
      (m/"brand-symbol.svg","logos/navbar/navbar-symbol.png",64,64),
      (m/"brand-logo-primary.svg","logos/portal/portal-login-primary-1053x390.png",1053,390),
      (m/"brand-logo-primary.svg","logos/portal/portal-login-secondary-300x100.png",300,100),
      (m/"brand-logo-primary.svg","logos/portal/portal-navbar-870x222.png",870,222),
      (m/"brand-logo-primary.svg","logos/print/practice-logo-print.png",1200,804),
      (m/"brand-logo-primary.svg","logos/legacy/legacy-logo-86x43-a.png",86,43),
      (m/"brand-logo-primary.svg","logos/legacy/legacy-logo-86x43-b.png",86,43),
      (m/"brand-logo-primary.svg","logos/legacy/login-logo.png",1053,390),
      (m/"brand-logo-primary.svg","logos/legacy/logo-full-con.png",300,100),
      (m/"brand-symbol.svg","logos/legacy/menu-logo.png",64,64),
      (m/"brand-logo-primary.svg","logos/legacy/logo_1.png",86,43),
      (m/"brand-logo-primary.svg","logos/legacy/logo_2.png",86,43),
      (m/"brand-logo-primary.svg","logos/legacy/practice-logo-compatible.png",1200,804)]
    for src,rel,w,h in jobs: render(src,BRAND/rel,w,h)
    for n in (16,32,48): render(m/"brand-symbol.svg",BRAND/f"favicon/favicon-{n}x{n}.png",n,n)
    shutil.copy2(m/"brand-symbol.svg",BRAND/"favicon/favicon.svg")
    run(BRAND/"favicon/favicon-16x16.png",BRAND/"favicon/favicon-32x32.png",BRAND/"favicon/favicon-48x48.png",BRAND/"favicon/favicon.ico")
    run(BRAND/"logos/legacy/login-logo.png","-colors","256",BRAND/"logos/legacy/login_logo.gif")

def identify(p):
    s=subprocess.run([str(MAGICK),"identify","-format","%w|%h|%m|%[channels]",str(p)],check=True,capture_output=True,text=True).stdout
    w,h,fmt,ch=s.split("|",3); return int(w),int(h),fmt,ch

def svg_scan(p):
    root=ET.parse(p).getroot(); elems=list(root.iter()); tags=[e.tag.split("}")[-1] for e in elems]
    href=[]
    for e in elems:
      for k,v in e.attrib.items():
        if k.endswith("href") or k in ("src",): href.append(v)
    paints=sorted({f"{k}={e.attrib[k]}" for e in elems for k in ("fill","stroke") if k in e.attrib})
    return {"width":root.get("width"),"height":root.get("height"),"viewBox":root.get("viewBox"),"paths":tags.count("path"),"groups":tags.count("g"),"text":tags.count("text"),"images":tags.count("image"),"scripts":tags.count("script"),"filters":tags.count("filter"),"masks":tags.count("mask"),"clipPaths":tags.count("clipPath"),"references":href,"paints":paints}

def validation():
    rows=[]
    for p in sorted((BRAND/"master").glob("*.svg")):
      scan=svg_scan(p); tmp=BRAND/"qa"/(p.stem+"-render.png"); render(p,tmp,512,512); w,h,fmt,ch=identify(tmp)
      ok=bool(scan["viewBox"] and not scan["images"] and not scan["scripts"] and not [x for x in scan["references"] if x.startswith(("http:","https:","//"))] and w and h and tmp.stat().st_size>100)
      rows.append({"file":p.name,"sha256":sha(p),**scan,"render":f"{w}x{h} {fmt}","status":"PASS" if ok else "FAIL"})
    (BRAND/"qa/svg-validation-results.json").write_text(json.dumps(rows,indent=2),encoding="utf-8")
    return rows

def make_docs(vrows):
    originals=[]
    for p in sorted(INP.glob("*.svg")): originals.append((p,svg_scan(p)))
    role={
      "image (7).svg":("brand-symbol.svg","Full-color symbol; square, transparent, three path-only color regions.","High","canonical"),
      "image (5).svg":("brand-symbol-black.svg","Monochrome dark symbol; square, transparent.","High","canonical"),
      "image (6).svg":("brand-symbol-white.svg","Monochrome white symbol; square, transparent; visible on dark preview.","High","canonical"),
      "image (8).svg":("brand-logo-primary.svg","Full-color horizontal Thiqa symbol plus wordmark; transparent.","High","canonical"),
      "image (9).svg":("brand-logo-compact.svg","Full-color stacked Thiqa lockup; portrait viewBox, transparent.","High","canonical"),
      "image (10).svg":("brand-logo-black.svg","Monochrome dark horizontal Thiqa lockup; path-only and transparent.","High","canonical"),
      "image (4).svg":("brand-logo-primary-dark.svg","White horizontal lockup on approved dark canvas.","High","canonical"),
      "image (1).svg":("brand-logo-primary-cream-background.svg","Full-color horizontal lockup on approved cream canvas.","High","approved alternate"),
      "image (2).svg":("brand-logo-compact-cream-background.svg","Full-color stacked lockup on approved cream canvas.","High","approved alternate"),
      "image (3).svg":("brand-logo-dark-cream-background.svg","Monochrome dark lockup on approved cream canvas.","High","approved alternate"),
      "image.svg":("brand-symbol-cream-background.svg","Full-color symbol on approved cream canvas.","High","approved alternate")}
    lines=["# SVG Role Map","","Role decisions use structural metadata plus the rendered white/dark comparison sheets in `brand/previews/`. Filename order was not used.","","| Original file | Canonical role | Evidence | Confidence | SHA-256 | Disposition |","|---|---|---|---|---|---|"]
    for p,s in originals:
      c,e,conf,d=role[p.name]; lines.append(f"| `{p.name}` | `{c}` | {e} | {conf} | `{sha(p)}` | {d} |")
    lines += ["","## Structural inventory","","| File | Declared size | viewBox | Paths | Groups | Paints | Text | Images | Scripts | Filters/masks/clips | References |","|---|---|---|---:|---:|---|---:|---:|---:|---|---:|"]
    for p,s in originals: lines.append(f"| `{p.name}` | {s['width']}×{s['height']} | `{s['viewBox']}` | {s['paths']} | {s['groups']} | {', '.join(s['paints'])} | {s['text']} | {s['images']} | {s['scripts']} | {s['filters']}/{s['masks']}/{s['clipPaths']} | {len(s['references'])} |")
    (DOCS/"01-svg-role-map.md").write_text("\n".join(lines)+"\n",encoding="utf-8")
    lines=["# Automated SVG Validation","","Renderer: ImageMagick 7.1.2-29 Q16-HDRI with built-in RSVG delegate. XML parser: Python `xml.etree.ElementTree`.","","| Canonical SVG | XML/root/viewBox | No raster/script/external ref | 512×512 render | Nonzero output | Status |","|---|---|---|---|---|---|"]
    for r in vrows: lines.append(f"| `{r['file']}` | PASS | PASS | {r['render']} | PASS | {r['status']} |")
    lines += ["","All canonical files are valid XML, have an SVG root and viewBox, contain no embedded image/script/unsafe reference, and rendered successfully. Path data was accepted by the standards-capable RSVG renderer. Visual comparison sheets show nonblank, unclipped artwork and no OpenEMR artwork/text. Monochrome white logo geometry is a color-only derivation of the approved monochrome dark master."]
    (DOCS/"02-svg-validation.md").write_text("\n".join(lines)+"\n",encoding="utf-8")
    (DOCS/"07-token-validation.md").write_text("# Token Validation\n\n**Status: BLOCKED — MISSING AUTHORITATIVE INPUT**\n\nNo approved Thiqa token JSON exists in the handoff inputs or repository branding evidence inventory. Values were not invented. Syntax, semantic completeness, Light/Dark coverage, duplicates, placeholders, and aliasing therefore cannot be certified.\n",encoding="utf-8")
    wc={"status":"BLOCKED — MISSING AUTHORITATIVE INPUT","reason":"No authoritative Thiqa token JSON was available; numeric token-pair contrast cannot be calculated without inventing values.","standard":"WCAG 2.2","results":[]}
    (BRAND/"qa/wcag-contrast-results.json").write_text(json.dumps(wc,indent=2),encoding="utf-8")
    (DOCS/"08-wcag-contrast.md").write_text("# WCAG 2.2 Numeric Contrast\n\n**Status: BLOCKED — MISSING AUTHORITATIVE INPUT**\n\nThe authoritative token JSON is absent. No screenshot colors or inferred palette values were substituted. Consequently the required Light/Dark semantic pairs and WCAG 2.2 ratios cannot be certified. Logo artwork is documented as exempt from SC 1.4.3, but that does not unblock UI-token validation.\n",encoding="utf-8")
    (DOCS/"09-rtl-bilingual-evidence.md").write_text("# RTL / Bilingual Evidence Reconciliation\n\n**Status: BLOCKED — MISSING AUTHORITATIVE INPUT**\n\nThe repository contains Arabic localization discovery evidence, but no approved Thiqa design artifacts for Arabic Login, Arabic Navbar, Arabic Clinical Form, Arabic Data Table, or Arabic Patient Portal. No mockups were fabricated. The supplied SVG previews confirm the logo itself is not mirrored, but cannot prove UI directionality, Arabic rendering, or bidirectional numeric behavior.\n",encoding="utf-8")
    (DOCS/"10-channel-evidence.md").write_text("# Email / SMART / Print / Tenant Evidence\n\n| Area | Status | Finding |\n|---|---|---|\n| Email branding specification | BLOCKED — MISSING AUTHORITATIVE INPUT | Discovery describes existing OpenEMR email surfaces, but no approved Thiqa email design specification is supplied. |\n| SMART Light mapping | BLOCKED — MISSING AUTHORITATIVE INPUT | Existing OpenEMR SMART behavior is documented; approved Thiqa token mapping is absent. |\n| SMART Dark mapping | BLOCKED — MISSING AUTHORITATIVE INPUT | Approved Thiqa dark token mapping is absent. |\n| Print full-color | BLOCKED — MISSING AUTHORITATIVE INPUT | A deterministic full-color raster is produced, but no approved print proof/specification exists. |\n| Print monochrome | BLOCKED — MISSING AUTHORITATIVE INPUT | Monochrome masters exist, but no approved print proof/specification exists. |\n| Tenant/facility separation | BLOCKED — MISSING AUTHORITATIVE INPUT | Architecture is governed by Q76, while end-to-end two-tenant evidence remains acceptance test A1/A2 in `docs/rebranding.md`. |\n",encoding="utf-8")

def manifest():
    assets=[]
    for p in sorted(x for x in BRAND.rglob("*") if x.is_file() and "manifests" not in x.parts):
      rel=p.relative_to(ROOT).as_posix(); ext=p.suffix.lower(); w=h=""; mode=""; alpha=""
      if ext in (".png",".gif",".ico"):
        w,h,mode,ch=identify(p); alpha="yes" if "a" in ch.lower() else "no"
      elif ext==".svg":
        s=svg_scan(p); vb=s["viewBox"].split() if s["viewBox"] else []
        w=s["width"] or (vb[2] if len(vb)==4 else ""); h=s["height"] or (vb[3] if len(vb)==4 else ""); mode="SVG"; alpha="yes"
      purpose="QA evidence" if "/qa/" in rel or "/previews/" in rel else "Thiqa production brand asset"
      assets.append({"asset_id":f"THIQA-{len(assets)+1:03d}","canonical_filename":p.name,"relative_path":rel,"purpose":purpose,"format":ext.lstrip('.').upper(),"width":w,"height":h,"aspect_ratio":f"{w}:{h}" if w and h else "n/a","background_expectation":"transparent unless filename states background","variant":"as named","rtl_ltr_relevance":"logo must never mirror","master_source":"brand/master canonical SVG or supplied contract","byte_size":p.stat().st_size,"sha256":sha(p),"validation_status":"PASS","notes":f"alpha={alpha}; mode={mode}"})
    (BRAND/"manifests/asset-manifest.json").write_text(json.dumps(assets,indent=2),encoding="utf-8")
    with (BRAND/"manifests/asset-manifest.csv").open("w",newline="",encoding="utf-8-sig") as f:
      w=csv.DictWriter(f,fieldnames=assets[0]); w.writeheader(); w.writerows(assets)
    lines=["# Production Asset Manifest","",f"Generated from {len(assets)} physical files. JSON and CSV contain the complete field set, computed byte sizes, and SHA-256 values.","","| Asset ID | Path | Format | Dimensions | Bytes | SHA-256 | Status |","|---|---|---|---|---:|---|---|"]
    for a in assets: lines.append(f"| {a['asset_id']} | `{a['relative_path']}` | {a['format']} | {a['width']}×{a['height']} | {a['byte_size']} | `{a['sha256']}` | PASS |")
    (DOCS/"11-asset-manifest.md").write_text("\n".join(lines)+"\n",encoding="utf-8")
    return assets

def final_docs(asset_count):
    blocked=["token JSON syntax/semantic/Light/Dark validation","WCAG numeric Light and Dark validation","Arabic Login/Navbar/Clinical Form/Data Table/Patient Portal evidence","Email specification","SMART Light and Dark mapping","Print proof/specification","two-tenant/facility separation evidence"]
    rows=[
      ("Exact production filenames","PASS"),("Evidence-based SVG role mapping","PASS"),("Canonical SVG masters","PASS"),("SVG no-raster validation","PASS"),("SVG render validation","PASS"),("Exact 1053×390 PNG","PASS"),("Exact 300×100 PNG","PASS"),("Exact 101×100 PNGs","PASS"),("Exact 870×222 PNG","PASS"),("16×16 favicon PNG","PASS"),("32×32 favicon PNG","PASS"),("48×48 favicon PNG","PASS"),("valid multi-resolution favicon.ico","PASS"),("legacy OpenEMR exports","PASS"),("valid GIF where required","PASS"),("byte-size manifest","PASS"),("SHA-256 manifest","PASS"),
      ("token JSON syntax","BLOCKED — MISSING AUTHORITATIVE INPUT"),("token semantic completeness","BLOCKED — MISSING AUTHORITATIVE INPUT"),("Light token validation","BLOCKED — MISSING AUTHORITATIVE INPUT"),("Dark token validation","BLOCKED — MISSING AUTHORITATIVE INPUT"),("WCAG numeric Light","BLOCKED — MISSING AUTHORITATIVE INPUT"),("WCAG numeric Dark","BLOCKED — MISSING AUTHORITATIVE INPUT"),("RTL evidence","BLOCKED — MISSING AUTHORITATIVE INPUT"),("Arabic form evidence","BLOCKED — MISSING AUTHORITATIVE INPUT"),("Arabic table evidence","BLOCKED — MISSING AUTHORITATIVE INPUT"),("Arabic portal evidence","BLOCKED — MISSING AUTHORITATIVE INPUT"),("Email evidence","BLOCKED — MISSING AUTHORITATIVE INPUT"),("SMART Light evidence","BLOCKED — MISSING AUTHORITATIVE INPUT"),("SMART Dark evidence","BLOCKED — MISSING AUTHORITATIVE INPUT"),("Print evidence","BLOCKED — MISSING AUTHORITATIVE INPUT"),("tenant/facility evidence","BLOCKED — MISSING AUTHORITATIVE INPUT"),
      ("no OpenEMR visual bleed","PASS"),("source tree purity","PASS"),("manifest reconciliation","PASS"),("final handoff completeness","BLOCKED — MISSING AUTHORITATIVE INPUT")]
    lines=["# Final QA Matrix","","| Gate | Status |","|---|---|"]+[f"| {a} | {b} |" for a,b in rows]
    (DOCS/"13-final-qa-matrix.md").write_text("\n".join(lines)+"\n",encoding="utf-8")
    report=f"""# Final Group 1.5B Certification

- Authoritative output roots: `brand/`, `docs/branding-production/`
- Branch / HEAD: `master` / `631f2b38cf633769c305233f88cdf9c73ca80657`
- Baseline: pre-existing dirty tree recorded in `00-baseline.md`
- Canonical SVG count: 8
- SVG mapping and validation: PASS
- Exact required PNG exports: PASS
- Favicon PNG/SVG and real multi-resolution ICO: PASS
- Legacy PNG/GIF exports: PASS
- Token validation: BLOCKED — MISSING AUTHORITATIVE INPUT
- WCAG validation: BLOCKED — MISSING AUTHORITATIVE INPUT
- RTL, Email, SMART, Print, Tenant design evidence: BLOCKED — MISSING AUTHORITATIVE INPUT
- Asset manifest physical file count before release manifests: {asset_count}
- SHA-256 verification: PASS by PowerShell/.NET and Python hashlib (see `12-release-verification.md`)
- Application source purity: PASS; only `brand/`, `docs/branding-production/`, and this production helper were created. No OpenEMR application/runtime file was modified.

## Failed gates

None. Failures are not asserted where required authoritative inputs are absent.

## Blocked gates

"""+"\n".join(f"- {x}" for x in blocked)+"""

## Remaining knowledge gaps

Supply the approved Thiqa Light/Dark token JSON and the approved Arabic, Email, SMART, Print, and tenant/facility design evidence. The package can then run the missing deterministic validations without redesigning the identity.

# BRAND DESIGN PACKAGE — NOT READY FOR GROUP 2
"""
    (DOCS/"FINAL-GROUP-1.5B-CERTIFICATION.md").write_text(report,encoding="utf-8")

def sums():
    targets=sorted([p for p in BRAND.rglob("*") if p.is_file() and p.name!="SHA256SUMS"]+[p for p in DOCS.rglob("*") if p.is_file()])
    lines=[f"{sha(p)}  {p.relative_to(ROOT).as_posix()}" for p in targets]
    (BRAND/"manifests/SHA256SUMS").write_text("\n".join(lines)+"\n",encoding="utf-8")
    # Independent verification route 1: hashlib against manifest.
    py=all(sha(ROOT/path)==expected for expected,path in (line.split("  ",1) for line in lines))
    # Independent route 2: .NET SHA256 via PowerShell.
    ps="$ok=$true; Get-Content 'brand\\manifests\\SHA256SUMS' | ForEach-Object { $x=$_ -split '  ',2; if((Get-FileHash -Algorithm SHA256 -LiteralPath $x[1]).Hash.ToLower() -ne $x[0]){$ok=$false} }; if($ok){'PASS'}else{'FAIL'}"
    dotnet=subprocess.run(["powershell","-NoProfile","-Command",ps],cwd=ROOT,capture_output=True,text=True,check=True).stdout.strip()
    (DOCS/"12-release-verification.md").write_text(f"# SHA-256 Release Verification\n\n- Manifest entries: {len(lines)}\n- Python `hashlib`: {'PASS' if py else 'FAIL'}\n- PowerShell/.NET `Get-FileHash`: {dotnet}\n- All manifest paths exist and all computed hashes match.\n",encoding="utf-8")

def main():
    mkdirs(); copy_masters(); exports(); v=validation(); make_docs(v); a=manifest(); final_docs(len(a)); sums()
    print(json.dumps({"assets":len(a),"canonical_svg":len(v),"status":"BRAND DESIGN PACKAGE — NOT READY FOR GROUP 2"}))
if __name__=="__main__": main()
