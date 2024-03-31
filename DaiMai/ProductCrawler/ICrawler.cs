using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace ProductCrawler
{
    interface ICrawler
    {
        public Task Get();

        public void GenerateFile();
    }
}
