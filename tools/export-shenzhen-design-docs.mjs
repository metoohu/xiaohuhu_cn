/**
 * 从 database/data/shenzhen_design_companies.json 生成 Excel 可用的 CSV（UTF-8 BOM）与空格分列 TXT。
 * 运行：node tools/export-shenzhen-design-docs.mjs
 */
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const root = path.resolve(__dirname, '..');
const jsonPath = path.join(root, 'database', 'data', 'shenzhen_design_companies.json');
const docsDir = path.join(root, 'docs');
const csvPath = path.join(docsDir, '深圳勘察设计企业名录.csv');
const txtPath = path.join(docsDir, '深圳勘察设计企业名录-空格分列.txt');

const rows = JSON.parse(fs.readFileSync(jsonPath, 'utf8'));

function csvCell(s) {
    const t = String(s ?? '');
    if (/[",\r\n]/.test(t)) return `"${t.replace(/"/g, '""')}"`;
    return t;
}

const header = ['公司名称', '联系电话', '地址', '主营业务'];
let csv = '\uFEFF' + header.map(csvCell).join(',') + '\n';
for (const r of rows) {
    csv += [r.name, r.phone, r.address, r.business].map(csvCell).join(',') + '\n';
}

fs.mkdirSync(docsDir, { recursive: true });
fs.writeFileSync(csvPath, csv, 'utf8');

let txt = '公司名称 联系电话 地址 主营业务\n';
for (const r of rows) {
    txt += `${r.name} ${r.phone} ${r.address} ${r.business}\n`;
}
fs.writeFileSync(txtPath, txt, 'utf8');

console.log(`Wrote ${csvPath} and ${txtPath} (${rows.length} rows)`);
