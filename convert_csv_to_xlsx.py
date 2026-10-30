import csv
import zipfile
import xml.sax.saxutils as saxutils
import os

def csv_to_xlsx(csv_path, xlsx_path, sheet_name="Sheet1"):
    rows = []
    with open(csv_path, 'r', encoding='utf-8-sig', errors='replace') as f:
        reader = csv.reader(f)
        for r in reader:
            if any(cell.strip() for cell in r):
                rows.append(r)

    if not rows:
        print(f"Empty CSV: {csv_path}")
        return

    # Build XML strings
    # 1. [Content_Types].xml
    content_types = """<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Default Extension="xml" ContentType="application/xml"/>
  <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
  <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
  <Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>
</Types>"""

    # 2. _rels/.rels
    rels = """<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
</Relationships>"""

    # 3. xl/_rels/workbook.xml.rels
    wb_rels = """<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
  <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>
</Relationships>"""

    # 4. xl/workbook.xml
    workbook = f"""<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
  <sheets>
    <sheet name="{sheet_name}" sheetId="1" r:id="rId1"/>
  </sheets>
</workbook>"""

    # 5. xl/styles.xml (with clean header styling & borders)
    styles = """<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
  <fonts count="2">
    <font><sz val="11"/><name val="Calibri"/></font>
    <font><b/><color rgb="FFFFFFFF"/><sz val="11"/><name val="Calibri"/></font>
  </fonts>
  <fills count="3">
    <fill><patternFill patternType="none"/></fill>
    <fill><patternFill patternType="gray125"/></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FF0A66FF"/></patternFill></fill>
  </fills>
  <borders count="2">
    <border><left/><right/><top/><bottom/></border>
    <border>
      <left style="thin"><color rgb="FFD1D5DB"/></left>
      <right style="thin"><color rgb="FFD1D5DB"/></right>
      <top style="thin"><color rgb="FFD1D5DB"/></top>
      <bottom style="thin"><color rgb="FFD1D5DB"/></bottom>
    </border>
  </borders>
  <cellStyleXfs count="1">
    <xf numFmtId="0" fontId="0" fillId="0" borderId="0"/>
  </cellStyleXfs>
  <cellXfs count="3">
    <xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>
    <xf numFmtId="0" fontId="1" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1">
      <alignment horizontal="center" vertical="center"/>
    </xf>
    <xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1" applyAlignment="1">
      <alignment vertical="center"/>
    </xf>
  </cellXfs>
</styleSheet>"""

    # 6. xl/worksheets/sheet1.xml
    def col_to_letter(col_idx):
        result = ""
        while col_idx > 0:
            col_idx, remainder = divmod(col_idx - 1, 26)
            result = chr(65 + remainder) + result
        return result

    sheet_rows_xml = []
    num_cols = max(len(r) for r in rows)

    # Column widths
    cols_xml = ['<cols>']
    for c in range(1, num_cols + 1):
        cols_xml.append(f'<col min="{c}" max="{c}" width="22" customWidth="1"/>')
    cols_xml.append('</cols>')

    for r_idx, row in enumerate(rows, start=1):
        cells_xml = []
        is_header = (r_idx == 1)
        style_id = "1" if is_header else "2"

        for c_idx, val in enumerate(row, start=1):
            ref = f"{col_to_letter(c_idx)}{r_idx}"
            escaped = saxutils.escape(str(val))
            cells_xml.append(f'<c r="{ref}" s="{style_id}" t="inlineStr"><is><t>{escaped}</t></is></c>')

        sheet_rows_xml.append(f'<row r="{r_idx}" ht="22" customHeight="1">{"".join(cells_xml)}</row>')

    sheet1 = f"""<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
  {"".join(cols_xml)}
  <sheetData>
    {"".join(sheet_rows_xml)}
  </sheetData>
</worksheet>"""

    # Create ZIP archive (XLSX)
    with zipfile.ZipFile(xlsx_path, 'w', compression=zipfile.ZIP_DEFLATED) as z:
        z.writestr("[Content_Types].xml", content_types)
        z.writestr("_rels/.rels", rels)
        z.writestr("xl/_rels/workbook.xml.rels", wb_rels)
        z.writestr("xl/workbook.xml", workbook)
        z.writestr("xl/styles.xml", styles)
        z.writestr("xl/worksheets/sheet1.xml", sheet1)

    print(f"Successfully generated Excel file: {xlsx_path}")

if __name__ == "__main__":
    base = r"D:\25CS044_WDF_PROJECT-main"
    files = [
        (os.path.join(base, "data", "students_export.csv"), os.path.join(base, "data", "students_export.xlsx"), "Students_Records"),
        (os.path.join(base, "data", "registrations.csv"), os.path.join(base, "data", "registrations.xlsx"), "Registrations"),
        (os.path.join(base, "data", "contacts.csv"), os.path.join(base, "data", "contacts.xlsx"), "Contacts"),
        (os.path.join(base, "php-form-handling", "data", "registrations.csv"), os.path.join(base, "php-form-handling", "data", "registrations.xlsx"), "Form_Registrations"),
        (os.path.join(base, "data", "students_export.csv"), os.path.join(base, "practical-11", "StudentHub_Students_Export.xlsx"), "Students_P11_Export")
    ]
    for csv_f, xlsx_f, sheet in files:
        if os.path.exists(csv_f):
            csv_to_xlsx(csv_f, xlsx_f, sheet)
