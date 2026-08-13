import fitz # PyMuPDF
doc_path = r'c:\Users\SoporteTI\Documents\GitHub\sistema-de-actas-icatec\manual-1.6.pdf'
try:
    doc = fitz.open(doc_path)
    # Check first few pages for signature examples
    for i in range(min(5, len(doc))):
        page = doc[i]
        text = page.get_text("dict")
        # Look for fonts
        for block in text["blocks"]:
            if "lines" in block:
                for line in block["lines"]:
                    for span in line["spans"]:
                        if "Firmado" in span["text"] or "Motivo" in span["text"]:
                            print(f"Page {i}: Font: {span['font']}, Size: {span['size']}, Text: {span['text']}")
    
    # Also extract images
    for i in range(min(5, len(doc))):
        page = doc[i]
        image_list = page.get_images(full=True)
        for image_index, img in enumerate(image_list):
            xref = img[0]
            base_image = doc.extract_image(xref)
            with open(f"manual_img_{i}_{image_index}.{base_image['ext']}", "wb") as f:
                f.write(base_image['image'])
except Exception as e:
    print('Error:', e)
