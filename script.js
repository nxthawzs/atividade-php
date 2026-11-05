document.addEventListener("DOMContentLoaded", () => {

  const selectProduto = document.getElementById("produto_id");
  const quantidade = document.getElementById("quantidade");
  const unitPrice = document.getElementById("unitPrice");
  const availableStock = document.getElementById("availableStock");
  const saleTotal = document.getElementById("saleTotal");
  const saleForm = document.getElementById("saleForm");

  const moeda = valor => Number(valor).toLocaleString("pt-BR", {
    style: "currency", currency: "BRL"
  });

  function atualizarVenda() {
    if (!selectProduto || !quantidade) return;
    const opcao = selectProduto.options[selectProduto.selectedIndex];
    const preco = Number(opcao?.dataset.preco || 0);
    const estoque = Number(opcao?.dataset.estoque || 0);
    const qtd = Math.max(0, Number(quantidade.value || 0));

    unitPrice.textContent = moeda(preco);
    availableStock.textContent = opcao?.value ? `${estoque} unidade(s)` : "—";
    saleTotal.textContent = moeda(preco * qtd);
    quantidade.max = estoque > 0 ? estoque : "";
  }

  if (selectProduto && quantidade) {
    selectProduto.addEventListener("change", atualizarVenda);
    quantidade.addEventListener("input", atualizarVenda);
    atualizarVenda();

    saleForm?.addEventListener("submit", event => {
      const opcao = selectProduto.options[selectProduto.selectedIndex];
      const estoque = Number(opcao?.dataset.estoque || 0);
      const qtd = Number(quantidade.value || 0);
      if (!opcao?.value || qtd < 1 || qtd > estoque) {
        event.preventDefault();
        alert("Selecione um produto e informe uma quantidade disponível em estoque.");
      }
    });
  }
});
