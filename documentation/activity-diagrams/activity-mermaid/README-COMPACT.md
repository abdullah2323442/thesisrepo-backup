# Compact Use Case Diagrams for A4 Printing

This directory contains multiple versions of the Use Case diagram optimized for A4 printing and documentation.

## 📊 Available Compact Versions

### 1. **Best A4 Version** (`16-use-case-best-a4.md`) ⭐ RECOMMENDED
- **Layout**: Flowchart with grouped features
- **Best For**: Professional documentation, thesis papers
- **Features**: 
  - Clean actor connections
  - Grouped by feature categories
  - Includes quick reference table
  - Balanced layout for A4
- **Print**: Portrait or Landscape

### 2. **Compact with Icons** (`16-use-case-compact.md`)
- **Layout**: Graph with emoji icons for actors
- **Best For**: Presentations, quick reference
- **Features**:
  - Visual actor identification
  - Simplified use cases
  - Color-coded sections
- **Print**: Portrait orientation

### 3. **Simplified Layout** (`16-use-case-simplified.md`)
- **Layout**: Left-to-right flow
- **Best For**: Process documentation
- **Features**:
  - Clear workflow visualization
  - Permission matrix included
  - Minimal connections
- **Print**: Landscape orientation

### 4. **Matrix Layout** (`16-use-case-matrix.md`)
- **Layout**: Grid-based by actor type
- **Best For**: Requirements documentation
- **Features**:
  - Organized by actor
  - Shows use case flow
  - Summary table included
- **Print**: Portrait orientation

### 5. **Ultra Compact** (`16-use-case-ultra-compact.md`)
- **Layout**: Mindmap and simplified graph
- **Best For**: Executive summaries, overview
- **Features**:
  - Extremely condensed
  - Quick reference card
  - Statistics included
- **Print**: Portrait orientation

## 🖨️ Printing Guidelines

### Recommended Settings

| Version | Orientation | Margins | Scale | Color |
|---------|------------|---------|-------|-------|
| Best A4 | Landscape | Normal | Fit to page | Color |
| Compact Icons | Portrait | Narrow | 100% | Color |
| Simplified | Landscape | Normal | Fit to page | B&W OK |
| Matrix | Portrait | Normal | 100% | Color |
| Ultra Compact | Portrait | Narrow | 100% | B&W OK |

### How to Print from Browser

1. Open the `.md` file in GitHub or render locally
2. Wait for Mermaid diagram to render
3. Press `Ctrl+P` (or `Cmd+P` on Mac)
4. Select printer settings:
   - Paper size: A4
   - Orientation: As recommended above
   - Margins: Normal or Narrow
   - Scale: Fit to page
5. Print or save as PDF

### Export as Image for Documents

```bash
# Using mermaid-cli
mmdc -i 16-use-case-best-a4.md -o use-case-a4.png -w 2480 -H 3508

# A4 dimensions at 300 DPI
# Width: 2480 pixels (210mm)
# Height: 3508 pixels (297mm)
```

## 📋 Choosing the Right Version

### Decision Matrix

| Use Case | Recommended Version | Why |
|----------|-------------------|-----|
| Thesis Documentation | Best A4 | Professional, comprehensive |
| Technical Specs | Matrix Layout | Organized by actor |
| Presentations | Compact Icons | Visual appeal |
| Quick Reference | Ultra Compact | Everything at a glance |
| Process Docs | Simplified | Clear workflows |

### Content Comparison

| Feature | Best A4 | Compact | Simplified | Matrix | Ultra |
|---------|---------|---------|------------|--------|-------|
| All Use Cases | ✅ | ✅ | ✅ | ✅ | Summary |
| Actor Icons | - | ✅ | - | - | - |
| Grouping | ✅ | ✅ | ✅ | ✅ | ✅ |
| Dependencies | ✅ | ✅ | ✅ | Partial | - |
| Reference Table | ✅ | ✅ | ✅ | ✅ | ✅ |
| Fits A4 | ✅ | ✅ | ✅ | ✅ | ✅ |

## 🎨 Customization Tips

### Adjust for Your Needs

1. **Change Colors**: Modify the style definitions
   ```mermaid
   style NodeID fill:#yourcolor
   ```

2. **Add/Remove Use Cases**: Edit the node definitions
   ```mermaid
   NewUC[Your Use Case]
   Actor --> NewUC
   ```

3. **Adjust Layout**: Change graph direction
   ```mermaid
   graph TB  %% Top-Bottom
   graph LR  %% Left-Right
   ```

4. **Resize for Different Paper**:
   - A3: Use full version with more details
   - Letter: Adjust margins in print settings
   - A5: Use ultra-compact version

## 📚 Integration Examples

### In LaTeX Documents
```latex
\begin{figure}[h]
  \centering
  \includegraphics[width=\textwidth]{use-case-a4.png}
  \caption{Thesis Management System Use Cases}
\end{figure}
```

### In Word Documents
1. Export diagram as PNG/SVG
2. Insert > Pictures > From File
3. Right-click > Size and Position
4. Set to 100% or fit to page

### In PowerPoint
1. Use compact or ultra-compact version
2. Export as SVG for scalability
3. Insert and resize as needed

## ✅ Quality Checklist

Before using in documentation:
- [ ] All actors are visible
- [ ] Use cases are readable
- [ ] Connections are clear
- [ ] Colors print well (test B&W)
- [ ] Fits on single A4 page
- [ ] Font size ≥ 9pt when printed
- [ ] No overlapping elements
- [ ] Legend/key included if needed

---

*Optimized for A4 (210×297mm) printing*
*Best viewed at 100% zoom*
*Recommended: Use **16-use-case-best-a4.md** for professional documentation*