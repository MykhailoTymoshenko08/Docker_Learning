import os
from langchain_community.document_loaders import PyPDFLoader
from langchain_text_splitters import RecursiveCharacterTextSplitter
from langchain_community.vectorstores import Chroma
from langchain_huggingface import HuggingFaceEmbeddings

pdf_path = os.path.join(os.path.dirname(__file__), "Daily Progress Tracker.pdf")
if not os.path.exists(pdf_path):
    print(f"Файл не знайдено: {pdf_path}")
    exit(1)

loader = PyPDFLoader(pdf_path)
pages = loader.load()
print(f"Завантажено {len(pages)} сторінок з PDF")

text_splitter = RecursiveCharacterTextSplitter(chunk_size=500, chunk_overlap=50)
chunks = text_splitter.split_documents(pages)

embeddings = HuggingFaceEmbeddings(model_name="sentence-transformers/all-MiniLM-L6-v2")

vector_db = Chroma.from_documents(
    documents=chunks, 
    embedding=embeddings, 
    persist_directory="./db_storage"
)

print(f"Документ розбито на {len(chunks)} шматочків та збережено у базу!")