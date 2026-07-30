import os
import re
from reportlab.lib.pagesizes import A4
from reportlab.lib import colors
from reportlab.platypus import SimpleDocTemplate, Paragraph, Spacer, Image, Table, TableStyle, PageBreak, HRFlowable
from reportlab.lib.styles import getSampleStyleSheet, ParagraphStyle
from reportlab.lib.units import inch

def create_pdf_from_md(md_filepath, output_pdf_path, title_text):
    print(f"Generating PDF: {output_pdf_path} from {md_filepath}")
    
    doc = SimpleDocTemplate(
        output_pdf_path,
        pagesize=A4,
        rightMargin=36,
        leftMargin=36,
        topMargin=36,
        bottomMargin=36
    )
    
    styles = getSampleStyleSheet()
    
    # Custom styles
    title_style = ParagraphStyle(
        'DocTitle',
        parent=styles['Heading1'],
        fontName='Helvetica-Bold',
        fontSize=20,
        leading=24,
        textColor=colors.HexColor('#1E293B'),
        spaceAfter=12
    )
    
    h1_style = ParagraphStyle(
        'CustomH1',
        parent=styles['Heading1'],
        fontName='Helvetica-Bold',
        fontSize=14,
        leading=18,
        textColor=colors.HexColor('#0F172A'),
        spaceBefore=14,
        spaceAfter=8
    )
    
    h2_style = ParagraphStyle(
        'CustomH2',
        parent=styles['Heading2'],
        fontName='Helvetica-Bold',
        fontSize=12,
        leading=15,
        textColor=colors.HexColor('#2563EB'),
        spaceBefore=10,
        spaceAfter=6
    )

    body_style = ParagraphStyle(
        'CustomBody',
        parent=styles['Normal'],
        fontName='Helvetica',
        fontSize=9.5,
        leading=13.5,
        textColor=colors.HexColor('#334155'),
        spaceAfter=6
    )
    
    code_style = ParagraphStyle(
        'CustomCode',
        parent=styles['Code'],
        fontName='Courier',
        fontSize=8,
        leading=11,
        textColor=colors.HexColor('#0F172A'),
        backColor=colors.HexColor('#F1F5F9'),
        spaceBefore=4,
        spaceAfter=6,
        leftIndent=10
    )

    story = []
    
    with open(md_filepath, 'r', encoding='utf-8') as f:
        lines = f.readlines()
        
    in_code_block = False
    code_buffer = []

    for line in lines:
        raw_line = line
        line = line.strip()
        
        if line.startswith('```'):
            if in_code_block:
                # Close code block
                code_text = "<br/>".join([c.replace('&', '&amp;').replace('<', '&lt;').replace('>', '&gt;') for c in code_buffer])
                story.append(Paragraph(code_text, code_style))
                code_buffer = []
                in_code_block = False
            else:
                in_code_block = True
            continue
            
        if in_code_block:
            code_buffer.append(raw_line.rstrip('\r\n'))
            continue

        if not line:
            story.append(Spacer(1, 4))
            continue
            
        # Headers
        if line.startswith('# '):
            story.append(Paragraph(line[2:].strip(), title_style))
            story.append(HRFlowable(width="100%", thickness=1.5, color=colors.HexColor('#2563EB'), spaceAfter=10))
            continue
        elif line.startswith('## '):
            story.append(Paragraph(line[3:].strip(), h1_style))
            story.append(HRFlowable(width="100%", thickness=0.8, color=colors.HexColor('#CBD5E1'), spaceAfter=6))
            continue
        elif line.startswith('### '):
            story.append(Paragraph(line[4:].strip(), h2_style))
            continue
            
        # Images: ![Caption](file:///...) or ![Caption](path)
        img_match = re.search(r'!\[(.*?)\]\((.*?)\)', line)
        if img_match:
            caption = img_match.group(1)
            img_path = img_match.group(2).replace('file:///', '')
            img_path = os.path.normpath(img_path)
            
            if os.path.exists(img_path):
                try:
                    img = Image(img_path, width=6.8*inch, height=3.5*inch)
                    story.append(Spacer(1, 4))
                    story.append(img)
                    story.append(Paragraph(f"<i>Gambar: {caption}</i>", ParagraphStyle('Cap', parent=body_style, fontSize=8, textColor=colors.HexColor('#64748B'), alignment=1)))
                    story.append(Spacer(1, 6))
                except Exception as e:
                    print(f"Error inserting image {img_path}: {e}")
            continue

        # Tables (Basic rendering for markdown tables)
        if line.startswith('|'):
            # Skip divider lines |---|---|
            if '---' in line:
                continue
            cols = [c.strip() for c in line.split('|')[1:-1]]
            if cols:
                table_data = [[Paragraph(c, body_style) for c in cols]]
                t = Table(table_data, colWidths=[6.8*inch / len(cols)] * len(cols))
                t.setStyle(TableStyle([
                    ('BACKGROUND', (0,0), (-1,-1), colors.HexColor('#F8FAFC')),
                    ('BOX', (0,0), (-1,-1), 0.5, colors.HexColor('#CBD5E1')),
                    ('INNERGRID', (0,0), (-1,-1), 0.5, colors.HexColor('#E2E8F0')),
                    ('TOPPADDING', (0,0), (-1,-1), 4),
                    ('BOTTOMPADDING', (0,0), (-1,-1), 4),
                ]))
                story.append(t)
                story.append(Spacer(1, 2))
            continue

        # Clean markdown bold/italic formatting for Paragraph
        formatted_line = line
        formatted_line = re.sub(r'\*\*(.*?)\*\*', r'<b>\1</b>', formatted_line)
        formatted_line = re.sub(r'\*(.*?)\*', r'<i>\1</i>', formatted_line)
        formatted_line = re.sub(r'`(.*?)`', r'<font face="Courier" color="#2563EB">\1</font>', formatted_line)
        
        story.append(Paragraph(formatted_line, body_style))
        
    doc.build(story)
    print(f"PDF Successfully created at: {output_pdf_path}")

if __name__ == "__main__":
    docs_dir = r"d:\Project\IstanaLaundryManagementSystem\docs"
    
    manual_md = os.path.join(docs_dir, "MANUAL_TESTING_GUIDE.md")
    manual_pdf = os.path.join(docs_dir, "MANUAL_TESTING_GUIDE.pdf")
    create_pdf_from_md(manual_md, manual_pdf, "Panduan Pengujian Manual UAT")
    
    qa_ai_md = os.path.join(docs_dir, "QA_AUTOMATION_AI_TESTING_GUIDE.md")
    qa_ai_pdf = os.path.join(docs_dir, "QA_AUTOMATION_AI_TESTING_GUIDE.pdf")
    create_pdf_from_md(qa_ai_md, qa_ai_pdf, "Panduan Otomatisasi AI QA Testing")
